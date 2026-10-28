<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/users.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
require_permission('delete_clients', $pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido';
    exit;
}
$clientId = intval($_POST['client_id'] ?? 0);
$result = delete_client_by_id($pdo, $clientId, $user['id']);
if (!$result['ok']) {
    $_SESSION['flash_error'] = $result['message'];
}
header('Location: /clientes.php');
exit;
