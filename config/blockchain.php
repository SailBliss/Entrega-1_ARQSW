<?php

return [
    'primary_node' => env('BLOCKCHAIN_PRIMARY_NODE', 'http://127.0.0.1:5001'),
    'nodes' => [
        'store' => env('BLOCKCHAIN_NODE_STORE', 'http://127.0.0.1:5001'),
        'auditor' => env('BLOCKCHAIN_NODE_AUDITOR', 'http://127.0.0.1:5002'),
        'notary' => env('BLOCKCHAIN_NODE_NOTARY', 'http://127.0.0.1:5003'),
    ],
    'timeout' => env('BLOCKCHAIN_TIMEOUT', 15),
    'issuer_token' => env('BLOCKCHAIN_ISSUER_TOKEN', ''),
];
