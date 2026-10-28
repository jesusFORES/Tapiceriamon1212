<?php
$dsn = 'mysql:host=' . (getenv('DB_HOST') ?: 'db') . ';dbname=' . (getenv('DB_NAME') ?: 'tapimon') . ';charset=utf8mb4';
$user = getenv('DB_USER') ?: 'tapimon';
$pass = getenv('DB_PASS') ?: 'tapimonpass';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $result = [];
    $result['modulo_exists'] = (bool) $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_schema = 'tapimon' AND table_name = 'modulo' LIMIT 1")->fetchColumn();
    $result['rm_pagina_fk'] = $pdo->query("SELECT column_name, referenced_table_name, referenced_column_name FROM information_schema.key_column_usage WHERE table_schema = 'tapimon' AND table_name = 'rm_pagina' AND referenced_table_name IS NOT NULL")->fetchAll();
    $result['rm_per_fk'] = $pdo->query("SELECT column_name, referenced_table_name, referenced_column_name FROM information_schema.key_column_usage WHERE table_schema = 'tapimon' AND table_name = 'rm_per' AND referenced_table_name IS NOT NULL")->fetchAll();
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit(1);
}
