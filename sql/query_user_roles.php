<?php
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    getenv('DB_HOST') ?: 'db',
    getenv('DB_NAME') ?: 'tapimon'
);
$user = getenv('DB_USER') ?: 'tapimon';
$pass = getenv('DB_PASS') ?: 'tapimonpass';

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    echo "USERS\n";
    $rows = $pdo->query('SELECT id, username, role_id FROM users')->fetchAll();
    foreach ($rows as $row) {
        echo implode('|', $row) . "\n";
    }
    echo "ROLES\n";
    $rows = $pdo->query('SELECT id, name FROM roles')->fetchAll();
    foreach ($rows as $row) {
        echo implode('|', $row) . "\n";
    }
    echo "USER_ROLES\n";
    $rows = $pdo->query('SELECT id, user_id, role_id FROM user_roles')->fetchAll();
    foreach ($rows as $row) {
        echo implode('|', $row) . "\n";
    }
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    exit(1);
}
