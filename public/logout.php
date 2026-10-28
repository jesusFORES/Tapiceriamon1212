<?php
require __DIR__ . '/../src/auth.php';
start_secure_session();
logout();
header('Location: /login.php');
exit;
?>
