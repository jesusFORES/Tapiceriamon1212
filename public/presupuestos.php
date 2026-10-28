<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/helpers.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
if ($user['role'] === 'Cliente') {
    require_permission('view_own_presupuestos', $pdo);
} else {
    require_permission('view_presupuestos', $pdo);
}
$errorMessage = $_SESSION['flash_error'] ?? '';
$successMessage = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
$presupuestos = get_presupuestos_for_user($pdo, $user);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Presupuestos - Tapimon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/dashboard_nav.php'; ?>
<div class="container my-4">
  <h3>Presupuestos</h3>
  <?php if ($errorMessage): ?>
    <div class="alert alert-danger"><?=e($errorMessage)?></div>
  <?php endif; ?>
  <?php if ($successMessage): ?>
    <div class="alert alert-success"><?=e($successMessage)?></div>
  <?php endif; ?>
  <?php if (has_permission('create_presupuestos')): ?>
    <a class="btn btn-primary mb-3" href="/presupuesto_create.php">Crear presupuesto</a>
  <?php endif; ?>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Fecha</th><th></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($presupuestos as $p): ?>
      <tr>
        <td><?=e($p['id'])?></td>
        <td><?=e($p['contact_name'])?></td>
        <td><?=number_format($p['total'],2)?></td>
        <td><?=e($p['estado'])?></td>
        <td><?=e($p['created_at'])?></td>
        <td><a class="btn btn-sm btn-outline-secondary" href="/presupuesto_view.php?id=<?=e($p['id'])?>">Ver</a></td>
        <td>
          <?php if (has_permission('delete_presupuestos')): ?>
            <form method="post" action="/delete_presupuesto.php" onsubmit="return confirm('¿Eliminar este presupuesto?');" style="display:inline-block;">
              <input type="hidden" name="presupuesto_id" value="<?=e($p['id'])?>">
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
