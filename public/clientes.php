<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/helpers.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
require_permission('manage_clients', $pdo);
$stmt = $pdo->query('SELECT c.*, u.username FROM clients c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC');
$clientes = $stmt->fetchAll();
$errorMessage = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clientes - Tapimon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/dashboard_nav.php'; ?>
<div class="container my-4">
  <h3>Clientes</h3>
  <?php if ($errorMessage): ?>
    <div class="alert alert-danger"><?=e($errorMessage)?></div>
  <?php endif; ?>
  <a class="btn btn-primary mb-3" href="/register.php">Registrar cliente</a>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Empresa</th><th>Contacto</th><th>Correo</th><th>Usuario</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($clientes as $c): ?>
      <tr>
        <td><?=e($c['id'])?></td>
        <td><?=e($c['company'])?></td>
        <td><?=e($c['contact_name'])?></td>
        <td><?=e($c['email'])?></td>
        <td><?=e($c['username'])?></td>
        <td>
          <?php if (has_permission('delete_clients')): ?>
            <form method="post" action="/delete_client.php" onsubmit="return confirm('Eliminar cliente y usuario asociado?');" style="display:inline-block;">
              <input type="hidden" name="client_id" value="<?=e($c['id'])?>">
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
