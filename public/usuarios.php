<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/helpers.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
require_permission('manage_users', $pdo);
$stmt = $pdo->query('SELECT u.id, u.username, u.full_name, u.email, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC');
$usuarios = $stmt->fetchAll();
$errorMessage = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Usuarios - Tapimon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/dashboard_nav.php'; ?>
<div class="container my-4">
  <h3>Usuarios</h3>
  <?php if ($errorMessage): ?>
    <div class="alert alert-danger"><?=e($errorMessage)?></div>
  <?php endif; ?>
  <a class="btn btn-primary mb-3" href="/register.php">Agregar usuario</a>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Correo</th><th>Rol</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?=e($u['id'])?></td>
        <td><?=e($u['username'])?></td>
        <td><?=e($u['full_name'])?></td>
        <td><?=e($u['email'])?></td>
        <td><?=e($u['role_name'])?></td>
        <td>
          <?php if ($u['id'] !== $user['id']): ?>
            <form method="post" action="/delete_user.php" onsubmit="return confirm('Eliminar este usuario?');" style="display:inline-block;">
              <input type="hidden" name="user_id" value="<?=e($u['id'])?>">
              <button class="btn btn-sm btn-danger">Eliminar</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
