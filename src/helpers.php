<?php
require_once __DIR__ . '/auth.php';

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function get_clients($pdo) {
    $stmt = $pdo->query('SELECT c.id, c.company, c.contact_name, u.username FROM clients c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.company');
    return $stmt->fetchAll();
}

function get_presupuestos_for_user($pdo, $user) {
    if ($user['role'] === 'Administrador') {
        $stmt = $pdo->query("SELECT p.*, c.contact_name FROM presupuestos p JOIN clients c ON p.cliente_id = c.id ORDER BY p.created_at DESC LIMIT 50");
        return $stmt->fetchAll();
    } elseif ($user['role'] === 'Empleado') {
        $stmt = $pdo->prepare("SELECT p.*, c.contact_name FROM presupuestos p JOIN clients c ON p.cliente_id = c.id WHERE p.empleado_id = ? ORDER BY p.created_at DESC LIMIT 50");
        $stmt->execute([$user['id']]);
        return $stmt->fetchAll();
    } else {
        // Cliente
        $stmt = $pdo->prepare("SELECT p.*, c.contact_name FROM presupuestos p JOIN clients c ON p.cliente_id = c.id WHERE c.user_id = ? ORDER BY p.created_at DESC LIMIT 50");
        $stmt->execute([$user['id']]);
        return $stmt->fetchAll();
    }
}

?>
