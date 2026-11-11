import hashlib
import hmac
import json
import os
import re
import sys
import tempfile
import time
from pathlib import Path
from threading import Lock, Thread
from urllib.parse import urlparse
from flask import Flask, jsonify, request
from flask_cors import CORS
import requests

PUBLIC_FIELDS = ('serial_number', 'certificate_hash', 'watch_name', 'watch_brand', 'issued_at')
PEER_TOKEN = os.environ.get('BLOCKCHAIN_PEER_TOKEN', '')
ISSUER_TOKEN = os.environ.get('BLOCKCHAIN_ISSUER_TOKEN', '')
CAN_MINE = os.environ.get('BLOCKCHAIN_CAN_MINE', 'true') == 'true'


def public_transaction(transaction):
    public = {key: transaction[key] for key in PUBLIC_FIELDS if isinstance(transaction.get(key), str)}
    if 'serial_number' in public:
        public['serial_number'] = re.sub(r'^(SN-W\d+)-O\d+-I\d+-', r'\1-O0-I0-', public['serial_number'])
    return public


def public_block(block):
    return {
        **{key: block[key] for key in ('index', 'timestamp', 'nonce', 'previous_hash', 'hash')},
        'transactions': [public_transaction(tx) for tx in block['transactions']],
    }


class Blockchain:
    def __init__(self, port, state_path, difficulty=3):
        self.chain = []
        self.current_transactions = []
        self.nodes = set()
        self.port = port
        self.difficulty = difficulty
        self.lock = Lock()
        self.state_path = Path(state_path)
        self.state_path.parent.mkdir(parents=True, exist_ok=True)
        try:
            with self.state_path.open(encoding='utf-8') as stream:
                state = json.load(stream)
        except FileNotFoundError:
            self.create_block(previous_hash='0' * 64, nonce=100, timestamp=0)
        else:
            if not self.valid_chain(state['chain']) or not self.valid_transactions(state['pending']):
                raise ValueError('Estado blockchain inválido')
            self.chain = state['chain']
            self.current_transactions = state['pending']

    def save_state(self, chain, pending):
        directory = self.state_path.parent
        with tempfile.NamedTemporaryFile(mode='w', encoding='utf-8', dir=directory, delete=False) as stream:
            temp_path = Path(stream.name)
            try:
                json.dump({'chain': chain, 'pending': pending}, stream, allow_nan=False)
                stream.flush()
                os.fsync(stream.fileno())
                stream.close()
                os.replace(temp_path, self.state_path)
                self.chain, self.current_transactions = chain, pending
                directory_fd = os.open(directory, os.O_RDONLY | os.O_DIRECTORY)
                try:
                    os.fsync(directory_fd)
                finally:
                    os.close(directory_fd)
            finally:
                temp_path.unlink(missing_ok=True)

    def register_node(self, address):
        """Agrega un nodo par a la lista de nodos conocidos"""
        parsed_url = urlparse(address)
        if parsed_url.netloc:
            self.nodes.add(parsed_url.netloc)
        elif parsed_url.path:
            self.nodes.add(parsed_url.path)

    def create_block(self, previous_hash, nonce, timestamp=None):
        """Crea un nuevo bloque en la cadena local"""
        block = {
            'index': len(self.chain) + 1,
            'timestamp': timestamp if timestamp is not None else time.time(),
            'transactions': self.current_transactions,
            'nonce': nonce,
            'previous_hash': previous_hash,
            'hash': ''
        }
        block['hash'] = self.hash_block(block)

        self.save_state(self.chain + [block], [])
        return block

    @staticmethod
    def hash_block(block):
        """Calcula el hash SHA-256 de un bloque ordenando sus llaves"""
        block_copy = {k: v for k, v in block.items() if k != 'hash'}
        block_string = json.dumps(block_copy, sort_keys=True).encode()
        return hashlib.sha256(block_string).hexdigest()

    @property
    def last_block(self):
        return self.chain[-1]

    def proof_of_work(self, previous_hash):
        """Proof of work: encontrar un nonce tal que el hash empiece con n ceros"""
        nonce = 0
        target_prefix = '0' * self.difficulty
        block_timestamp = time.time()
        candidate_block = {
            'index': len(self.chain) + 1,
            'timestamp': block_timestamp,
            'transactions': self.current_transactions,
            'previous_hash': previous_hash,
            'nonce': nonce
        }

        while True:
            candidate_block['nonce'] = nonce
            block_hash = self.hash_block(candidate_block)
            if block_hash.startswith(target_prefix):
                return nonce, block_timestamp, block_hash
            nonce += 1

    @staticmethod
    def valid_transactions(transactions):
        return isinstance(transactions, list) and all(
            isinstance(tx, dict) and all(isinstance(tx.get(key), str) and tx[key]
                                         for key in ('serial_number', 'certificate_hash'))
            for tx in transactions
        )

    def valid_block_data(self, block):
        return (
            isinstance(block, dict)
            and type(block.get('index')) is int
            and type(block.get('timestamp')) in (int, float)
            and type(block.get('nonce')) is int
            and self.valid_transactions(block.get('transactions'))
            and block.get('hash') == self.hash_block(block)
        )

    def valid_block(self, block, previous_block):
        """Valida que un bloque recibido cumpla con hash previo y dificultad"""
        if not self.valid_block_data(block) or block.get('previous_hash') != previous_block['hash']:
            return False
        if block['index'] != previous_block['index'] + 1:
            return False

        if not block['hash'].startswith('0' * self.difficulty):
            return False

        return True

    def valid_chain(self, chain):
        """Valida que toda una cadena sea criptográficamente íntegra"""
        if not isinstance(chain, list) or not chain:
            return False
        last_block = chain[0]
        if (
            not self.valid_block_data(last_block)
            or last_block['index'] != 1
            or last_block.get('previous_hash') != '0' * 64
            or last_block['transactions']
        ):
            return False
        current_index = 1

        while current_index < len(chain):
            block = chain[current_index]
            if not self.valid_block(block, last_block):
                return False
            last_block = block
            current_index += 1

        return True

    def resolve_conflicts(self):
        """Algoritmo de Consenso: Reemplaza la cadena con la válida más larga en la red"""
        with self.lock:
            neighbours = tuple(self.nodes)
        candidates = []
        for node in neighbours:
            try:
                response = requests.get(f'http://{node}/internal/chain', timeout=2,
                                        headers={'Authorization': f'Bearer {PEER_TOKEN}'},
                                        allow_redirects=False)
                if response.status_code == 200:
                    chain = response.json().get('chain', [])
                    if self.valid_chain(chain):
                        candidates.append(chain)
            except requests.RequestException:
                continue

        with self.lock:
            for chain in sorted(candidates, key=len, reverse=True):
                if len(chain) <= len(self.chain):
                    break
                if len(self.chain) == 1 or chain[:len(self.chain)] == self.chain:
                    self.accept_chain(chain)
                    return True
                app.logger.error('Cadena divergente: se conserva la historia local')
        return False

    def accept_chain(self, chain):
        new_blocks = chain[len(self.chain):]
        mined = {tx['certificate_hash'] for block in new_blocks for tx in block['transactions']}
        pending = [tx for tx in self.current_transactions if tx['certificate_hash'] not in mined]
        self.save_state(chain, pending)

    def broadcast_block(self, block):
        """Propaga un bloque recién minado a todos los nodos pares"""
        with self.lock:
            neighbours = tuple(self.nodes)
        for node in neighbours:
            try:
                requests.post(
                    f'http://{node}/blocks/receive',
                    json={'block': block},
                    headers={'Authorization': f'Bearer {PEER_TOKEN}'},
                    timeout=2,
                    allow_redirects=False,
                )
            except requests.RequestException:
                pass

    def add_transaction(self, transaction_data):
        """Agrega una transacción de certificado a la lista de pendientes"""
        certificate_hash = transaction_data['certificate_hash']
        for block in reversed(self.chain):
            if any(tx['certificate_hash'] == certificate_hash for tx in block['transactions']):
                return block['index']
        if not any(tx['certificate_hash'] == certificate_hash for tx in self.current_transactions):
            self.save_state(self.chain, self.current_transactions + [transaction_data])
        return self.last_block['index'] + 1


app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})
node_port = int(os.environ.get('PORT', sys.argv[1] if len(sys.argv) > 1 else 5001))
blockchain = Blockchain(port=node_port, state_path=os.environ.get('BLOCKCHAIN_STATE_PATH', 'data/state.json'))

# Auto-registro de pares configurados por variable de entorno
peers_env = os.environ.get('PEERS', '')
if peers_env:
    for peer in peers_env.split(','):
        if peer.strip():
            blockchain.register_node(peer.strip())


@app.before_request
def authorize_internal_routes():
    if request.method == 'OPTIONS':
        return None
    if request.endpoint in {'new_transaction', 'mine'}:
        token = ISSUER_TOKEN
    elif request.endpoint in {'receive_block', 'internal_chain', 'register_nodes', 'consensus'}:
        token = PEER_TOKEN
    else:
        return None
    authorization = request.headers.get('Authorization', '').encode()
    if not token or not hmac.compare_digest(authorization, f'Bearer {token}'.encode()):
        return jsonify({'error': 'Acceso restringido'}), 401


@app.route('/status', methods=['GET'])
def status():
    return jsonify({
        'status': 'online',
        'service': 'Universal Blockchain Certificate Authority',
        'node_port': blockchain.port,
        'chain_length': len(blockchain.chain),
        'difficulty': blockchain.difficulty,
    }), 200


@app.route('/transactions/new', methods=['POST'])
def new_transaction():
    if not CAN_MINE:
        return jsonify({'error': 'La emisión se realiza en el nodo principal'}), 403
    values = request.get_json(silent=True)
    if not blockchain.valid_transactions([values]):
        return jsonify({
            'error': 'serial_number y certificate_hash son obligatorios y deben ser texto.'
        }), 400

    transaction = public_transaction(values)
    with blockchain.lock:
        index = blockchain.add_transaction(transaction)
    return jsonify({
        'message': f'Transacción agregada exitosamente para el Bloque #{index}',
        'transaction': transaction
    }), 201


@app.route('/mine', methods=['POST', 'GET'])
def mine():
    if not CAN_MINE:
        return jsonify({'error': 'La emisión se realiza en el nodo principal'}), 403
    values = request.get_json(silent=True)
    certificate_hash = values.get('certificate_hash') if isinstance(values, dict) else None
    if len(blockchain.chain) == 1 and blockchain.nodes:
        blockchain.resolve_conflicts()
    with blockchain.lock:
        if certificate_hash:
            for block in reversed(blockchain.chain):
                if any(tx['certificate_hash'] == certificate_hash for tx in block['transactions']):
                    return jsonify({'message': 'Certificado ya minado', 'block': public_block(block)}), 200
        if not blockchain.current_transactions:
            return jsonify({'message': 'No hay transacciones pendientes para minar'}), 400
        if certificate_hash and not any(tx['certificate_hash'] == certificate_hash
                                        for tx in blockchain.current_transactions):
            return jsonify({'error': 'Certificado no encontrado'}), 404
        previous_hash = blockchain.last_block['hash']
        nonce, block_timestamp, _ = blockchain.proof_of_work(previous_hash)
        block = blockchain.create_block(previous_hash, nonce, timestamp=block_timestamp)

    # Propagar a nodos pares (Gossip)
    blockchain.broadcast_block(block)

    return jsonify({
        'message': 'Nuevo bloque minado y propagado con éxito',
        'block': public_block(block)
    }), 200


@app.route('/blocks/receive', methods=['POST'])
def receive_block():
    values = request.get_json(silent=True)
    block = values.get('block') if isinstance(values, dict) else None
    if not block:
        return jsonify({'error': 'Bloque no provisto'}), 400

    with blockchain.lock:
        if blockchain.valid_block(block, blockchain.last_block):
            blockchain.accept_chain(blockchain.chain + [block])
            return jsonify({'message': 'Bloque aceptado y anexado a la cadena'}), 200

    return jsonify({'message': 'El bloque no continúa la cadena local'}), 409


@app.route('/chain', methods=['GET'])
def full_chain():
    chain = blockchain.chain
    return jsonify({
        'chain': [public_block(block) for block in chain],
        'length': len(chain),
        'is_valid': blockchain.valid_chain(chain)
    }), 200


@app.route('/internal/chain', methods=['GET'])
def internal_chain():
    chain = blockchain.chain
    return jsonify({'chain': chain, 'length': len(chain)}), 200


@app.route('/nodes/register', methods=['POST'])
def register_nodes():
    values = request.get_json(silent=True)
    nodes = values.get('nodes') if isinstance(values, dict) else None
    if not isinstance(nodes, list) or not nodes or any(not isinstance(node, str) for node in nodes):
        return jsonify({'error': 'Lista de nodos inválida'}), 400
    if any((urlparse(node).netloc or urlparse(node).path) not in blockchain.nodes for node in nodes):
        return jsonify({'error': 'Configura los pares autorizados mediante PEERS'}), 403

    return jsonify({
        'message': 'Nuevos nodos registrados',
        'total_nodes': list(blockchain.nodes)
    }), 201


@app.route('/nodes/resolve', methods=['GET'])
def consensus():
    replaced = blockchain.resolve_conflicts()
    if replaced:
        response = {
            'message': 'Nuestra cadena fue reemplazada por la cadena válida más larga',
            'new_chain': [public_block(block) for block in blockchain.chain],
            'length': len(blockchain.chain)
        }
    else:
        response = {
            'message': 'Nuestra cadena es autoritativa y actual',
            'chain': [public_block(block) for block in blockchain.chain],
            'length': len(blockchain.chain)
        }
    return jsonify(response), 200


@app.route('/verify/<certificate_hash>', methods=['GET'])
def verify_certificate(certificate_hash):
    """Busca un certificado por su hash y valida la integridad de la cadena"""
    chain = blockchain.chain
    chain_valid = blockchain.valid_chain(chain)

    found_tx = None
    found_block = None

    for block in chain:
        for tx in block.get('transactions', []):
            if tx.get('certificate_hash') == certificate_hash:
                found_tx = tx
                found_block = block
                break
        if found_tx:
            break

    if not found_tx:
        return jsonify({
            'valid': False,
            'certificate_hash': certificate_hash,
            'message': 'Certificado no encontrado en esta blockchain'
        }), 404

    metadata = public_transaction(found_tx)
    response = {
        'valid': chain_valid,
        'certificate_hash': certificate_hash,
        'serial_number': metadata.get('serial_number'),
        'block_index': found_block.get('index'),
        'block_hash': found_block.get('hash'),
        'previous_hash': found_block.get('previous_hash'),
        'nonce': found_block.get('nonce'),
        'chain_total_blocks': len(chain),
        'mined_by_port': blockchain.port,
        'issued_at': metadata.get('issued_at'),
        'metadata': metadata
    }
    # Backward compatibility with existing store fields
    for legacy_key in ('watch_name', 'watch_brand'):
        if legacy_key in metadata:
            response[legacy_key] = metadata[legacy_key]

    return jsonify(response), 200


def synchronize_peers():
    while True:
        try:
            blockchain.resolve_conflicts()
        except (OSError, ValueError):
            app.logger.exception('Error al sincronizar la cadena')
        time.sleep(5)


if __name__ == '__main__':
    if PEER_TOKEN and PEER_TOKEN == ISSUER_TOKEN:
        raise ValueError('Los tokens de pares y de emisión deben ser distintos')
    if blockchain.nodes and PEER_TOKEN:
        Thread(target=synchronize_peers, daemon=True).start()
    app.run(host='0.0.0.0', port=node_port, debug=False)
