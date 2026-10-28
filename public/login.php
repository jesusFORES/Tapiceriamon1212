<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
start_secure_session();
$pdo = require __DIR__ . '/../src/db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (attempt_login($pdo, $username, $password)) {
        header('Location: /dashboard.php');
        exit;
    } else {
        $errors[] = 'Usuario o contraseña incorrectos';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Tapimon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f8f9fa}</style>
</head>
<body>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow">
        <div class="card-body p-4">
          <h4 class="mb-3">Iniciar sesión - Tapimon</h4>
          <?php if ($errors): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($errors[0])?></div>
          <?php endif; ?>
          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label">Usuario</label>
              <input name="username" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input name="password" type="password" class="form-control" required>
            </div>
            <div class="d-grid">
              <button class="btn btn-primary">Entrar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
