<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
start_secure_session();
$pdo = require __DIR__ . '/../src/db.php';
require_login();
$user = current_user();
$role = $user['role'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard - Tapimon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/dashboard_nav.php'; ?>
<div class="container my-4">
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Resumen</h5>
      <p>Rol: <strong><?=htmlspecialchars($role)?></strong></p>
      <?php if (has_permission('manage_users')): ?>
        <p>Acceso total: gestiona usuarios, clientes, presupuestos y mensajes.</p>
      <?php elseif (has_permission('create_presupuestos')): ?>
        <p>Puede ver presupuestos, responder a mensajes del cliente y crear nuevos presupuestos.</p>
      <?php else: ?>
        <p>Puede ver sus presupuestos, subir evidencias y conversar con el equipo sobre cada cotización.</p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($role === 'Administrador' || $role === 'Empleado'): ?>
  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Acciones rápidas</h5>
      <a class="btn btn-primary me-2" href="/register.php">Registrar cliente o empleado</a>
      <a class="btn btn-outline-secondary" href="/clientes.php">Ver clientes</a>
    </div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Presupuestos recientes</h5>
      <p class="text-muted">Abre un presupuesto para chatear con el equipo y adjuntar imágenes.</p>
    </div>
  </div>
</div>
</body>
</html>
