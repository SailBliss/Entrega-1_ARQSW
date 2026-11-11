import hashlib
import json
import os
import sys
import time
from urllib.parse import urlparse
from flask import Flask, jsonify, request
import requests

class Blockchain:
    def __init__(self, port, difficulty=3):
        self.chain = []
        self.current_transactions = []
        self.nodes = set()
        self.port = port
        self.difficulty = difficulty

        # Crear bloque génesis
        self.create_block(previous_hash='0' * 64, nonce=100)

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

        self.current_transactions = []
        self.chain.append(block)
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

    def valid_block(self, block, previous_block):
        """Valida que un bloque recibido cumpla con hash previo y dificultad"""
        if block['previous_hash'] != previous_block['hash']:
            return False
        if block['index'] != previous_block['index'] + 1:
            return False

        calculated_hash = self.hash_block(block)
        if calculated_hash != block['hash']:
            return False

        if not block['hash'].startswith('0' * self.difficulty):
            return False

        return True

    def valid_chain(self, chain):
        """Valida que toda una cadena sea criptográficamente íntegra"""
        last_block = chain[0]
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
        neighbours = self.nodes
        new_chain = None
        max_length = len(self.chain)

        for node in neighbours:
            try:
                response = requests.get(f'http://{node}/chain', timeout=2)
                if response.status_code == 200:
                    data = response.json()
                    length = data.get('length', 0)
                    chain = data.get('chain', [])

                    if length > max_length and self.valid_chain(chain):
                        max_length = length
                        new_chain = chain
            except requests.RequestException:
                continue

        if new_chain:
            self.chain = new_chain
            return True

        return False

    def broadcast_block(self, block):
        """Propaga un bloque recién minado a todos los nodos pares"""
        for node in self.nodes:
            try:
                requests.post(
                    f'http://{node}/blocks/receive',
                    json={'block': block},
                    timeout=2
                )
            except requests.RequestException:
                pass

    def add_transaction(self, transaction_data):
        """Agrega una transacción de certificado a la lista de pendientes"""
        self.current_transactions.append(transaction_data)
        return self.last_block['index'] + 1


app = Flask(__name__)
node_port = int(os.environ.get('PORT', sys.argv[1] if len(sys.argv) > 1 else 5001))
blockchain = Blockchain(port=node_port, difficulty=3)

# Auto-registro de pares configurados por variable de entorno
peers_env = os.environ.get('PEERS', '')
if peers_env:
    for peer in peers_env.split(','):
        if peer.strip():
            blockchain.register_node(peer.strip())


@app.route('/status', methods=['GET'])
def status():
    return jsonify({
        'status': 'online',
        'node_port': blockchain.port,
        'chain_length': len(blockchain.chain),
        'difficulty': blockchain.difficulty,
        'peers': list(blockchain.nodes)
    }), 200


@app.route('/transactions/new', methods=['POST'])
def new_transaction():
    values = request.get_json(silent=True) or {}
    required = ['order_id', 'watch_id', 'serial_number', 'certificate_hash']
    if not all(k in values for k in required):
        return jsonify({'error': f'Faltan campos requeridos: {required}'}), 400

    index = blockchain.add_transaction(values)
    return jsonify({
        'message': f'Transacción agregada para el Bloque #{index}',
        'transaction': values
    }), 201


@app.route('/mine', methods=['POST', 'GET'])
def mine():
    if not blockchain.current_transactions:
        return jsonify({'message': 'No hay transacciones pendientes para minar'}), 400

    last_block = blockchain.last_block
    previous_hash = last_block['hash']
    nonce, block_timestamp, block_hash = blockchain.proof_of_work(previous_hash)

    block = blockchain.create_block(previous_hash, nonce, timestamp=block_timestamp)

    # Propagar a nodos pares (Gossip)
    blockchain.broadcast_block(block)

    return jsonify({
        'message': 'Nuevo bloque minado y propagado con éxito',
        'block': block
    }), 200


@app.route('/blocks/receive', methods=['POST'])
def receive_block():
    values = request.get_json(silent=True) or {}
    block = values.get('block')
    if not block:
        return jsonify({'error': 'Bloque no provisto'}), 400

    last_block = blockchain.last_block
    if blockchain.valid_block(block, last_block):
        blockchain.chain.append(block)
        # Limpiar transacciones pendientes que ya están en el bloque
        tx_hashes = [tx.get('certificate_hash') for tx in block.get('transactions', [])]
        blockchain.current_transactions = [
            tx for tx in blockchain.current_transactions
            if tx.get('certificate_hash') not in tx_hashes
        ]
        return jsonify({'message': 'Bloque aceptado y anexado a la cadena'}), 200

    # Si no es directamente válido, intentar sincronizar consenso
    blockchain.resolve_conflicts()
    return jsonify({'message': 'Bloque recibido, sincronización ejecutada'}), 200


@app.route('/chain', methods=['GET'])
def full_chain():
    return jsonify({
        'chain': blockchain.chain,
        'length': len(blockchain.chain),
        'is_valid': blockchain.valid_chain(blockchain.chain)
    }), 200


@app.route('/nodes/register', methods=['POST'])
def register_nodes():
    values = request.get_json(silent=True) or {}
    nodes = values.get('nodes', [])
    if not nodes:
        return jsonify({'error': 'Lista de nodos vacía'}), 400

    for node in nodes:
        blockchain.register_node(node)

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
            'new_chain': blockchain.chain,
            'length': len(blockchain.chain)
        }
    else:
        response = {
            'message': 'Nuestra cadena es autoritativa y actual',
            'chain': blockchain.chain,
            'length': len(blockchain.chain)
        }
    return jsonify(response), 200


@app.route('/verify/<certificate_hash>', methods=['GET'])
def verify_certificate(certificate_hash):
    """Busca un certificado por su hash y valida la integridad de la cadena"""
    chain_valid = blockchain.valid_chain(blockchain.chain)

    found_tx = None
    found_block = None

    for block in blockchain.chain:
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

    return jsonify({
        'valid': chain_valid,
        'certificate_hash': certificate_hash,
        'serial_number': found_tx.get('serial_number'),
        'watch_name': found_tx.get('watch_name'),
        'watch_brand': found_tx.get('watch_brand'),
        'order_id': found_tx.get('order_id'),
        'issued_at': found_tx.get('issued_at'),
        'block_index': found_block.get('index'),
        'block_hash': found_block.get('hash'),
        'previous_hash': found_block.get('previous_hash'),
        'nonce': found_block.get('nonce'),
        'chain_total_blocks': len(blockchain.chain),
        'mined_by_port': blockchain.port
    }), 200


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=node_port, debug=False)
