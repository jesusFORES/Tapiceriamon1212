<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/users.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
require_permission('manage_users', $pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido';
    exit;
}
$userId = intval($_POST['user_id'] ?? 0);
$result = delete_user_by_id($pdo, $userId, $user['id']);
if (!$result['ok']) {
    $_SESSION['flash_error'] = $result['message'];
}
header('Location: /usuarios.php');
exit;
