<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/presupuesto.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
require_permission('delete_presupuestos', $pdo);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido';
    exit;
}
$presupuestoId = intval($_POST['presupuesto_id'] ?? 0);
$result = delete_presupuesto_by_id($pdo, $presupuestoId, $user['id']);
if (!$result['ok']) {
    $_SESSION['flash_error'] = $result['message'];
} else {
    $_SESSION['flash_success'] = $result['message'];
}
header('Location: /presupuestos.php');
exit;
