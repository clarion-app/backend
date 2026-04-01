<?php

return [
    'node_id' => env('CLARION_NODE_ID'),
    'frontend_url' => env('FRONTEND_URL'),
    'ssl_key' => env('SSL_KEY'),
    'ssl_cert' => env('SSL_CERT'),
    'store_url' => env('STORE_URL', 'https://store-api.clarion.app'),
    'multichain_binary_path' => env('MULTICHAIN_BINARY_PATH', '/usr/local/bin'),
    'multichain_datadir' => env('MULTICHAIN_DATADIR', '/var/www/.multichain'),
];
