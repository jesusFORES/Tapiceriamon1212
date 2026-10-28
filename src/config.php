<?php
// Configuración básica
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'name' => getenv('DB_NAME') ?: 'tapimon',
        'user' => getenv('DB_USER') ?: 'tapimon',
        'pass' => getenv('DB_PASS') ?: 'tapimonpass',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'base_url' => '/',
    ]
];
