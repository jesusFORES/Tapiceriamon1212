<?php
// Redirect to dashboard or login
require __DIR__ . '/../src/auth.php';
start_secure_session();
if (!empty($_SESSION['user'])) {
    header('Location: /dashboard.php');
    exit;
}
header('Location: /login.php');
exit;
?>
