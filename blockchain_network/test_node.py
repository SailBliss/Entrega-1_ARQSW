import json
import os
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

with tempfile.TemporaryDirectory() as bootstrap, patch.dict(os.environ, {
    'PORT': '5001',
    'BLOCKCHAIN_STATE_PATH': f'{bootstrap}/state.json',
    'BLOCKCHAIN_PEER_TOKEN': 'test-peer',
    'BLOCKCHAIN_ISSUER_TOKEN': 'test-issuer',
    'BLOCKCHAIN_CAN_MINE': 'true',
    'PEERS': '',
}):
    import node


class BlockchainTest(unittest.TestCase):
    def setUp(self):
        directory = self.enterContext(tempfile.TemporaryDirectory())
        self.path = Path(directory) / 'state.json'
        self.chain = node.Blockchain(5001, self.path)
        self.enterContext(patch.object(node, 'blockchain', self.chain))
        self.client = node.app.test_client()

    def test_legacy_data_survives_restart_and_public_views_are_redacted(self):
        legacy = {
            'serial_number': 'SN-W1-O123-I456-LEGACY',
            'certificate_hash': 'a' * 64,
            'buyer_email': 'legacy@example.test',
            'buyer_id': 123,
            'order_id': 123,
            'payload': {'buyer_name': 'Legacy Buyer'},
            'watch_name': 'Watch',
        }
        self.chain.add_transaction(legacy)
        reloaded = node.Blockchain(5001, self.path)
        self.assertEqual(reloaded.current_transactions, [legacy])
        previous_hash = reloaded.last_block['hash']
        nonce, timestamp, _ = reloaded.proof_of_work(previous_hash)
        original = reloaded.create_block(previous_hash, nonce, timestamp)
        restored = node.Blockchain(5001, self.path)
        self.assertEqual(restored.last_block, original)
        with patch.object(node, 'blockchain', restored):
            verification = self.client.get('/verify/' + legacy['certificate_hash']).json
            self.assertTrue(verification['valid'])
            self.assertEqual(verification['block_hash'], original['hash'])
            self.assertEqual(verification['serial_number'], 'SN-W1-O0-I0-LEGACY')
            public_block = self.client.get('/chain').json['chain'][1]
            self.assertEqual(public_block['transactions'], [verification['metadata']])
            self.assertEqual(set(verification['metadata']), {'serial_number', 'certificate_hash', 'watch_name'})
            self.assertNotIn('order_id', verification)
            raw = self.client.get('/internal/chain', headers={'Authorization': 'Bearer test-peer'})
            self.assertEqual(raw.json['chain'][1], original)

    def test_internal_endpoints_require_the_right_token_and_issuance_filters_input(self):
        for method, path in [
            ('GET', '/internal/chain'), ('GET', '/nodes/resolve'), ('POST', '/nodes/register'),
            ('POST', '/blocks/receive'), ('POST', '/transactions/new'), ('GET', '/mine'), ('POST', '/mine'),
        ]:
            with self.subTest(method=method, path=path):
                self.assertEqual(self.client.open(path, method=method).status_code, 401)
        issuer = {'Authorization': 'Bearer test-issuer'}
        self.assertEqual(self.client.get('/internal/chain', headers=issuer).status_code, 401)
        transaction = {'serial_number': 'PUBLIC', 'certificate_hash': 'b' * 64}
        response = self.client.post('/transactions/new', headers=issuer, json={
            **transaction, 'buyer_email': 'new@example.test', 'payload': {'buyer_id': 123},
        })
        self.assertEqual(response.status_code, 201)
        self.assertEqual(response.json['transaction'], transaction)
        self.assertEqual(node.Blockchain(5001, self.path).current_transactions, [transaction])

    def test_invalid_existing_state_is_preserved(self):
        state = json.loads(self.path.read_text(encoding='utf-8'))
        state['chain'][0]['hash'] = '0' * 64
        corrupted = json.dumps(state)
        self.path.write_text(corrupted, encoding='utf-8')
        with self.assertRaises(ValueError):
            node.Blockchain(5001, self.path)
        self.assertEqual(self.path.read_text(encoding='utf-8'), corrupted)
