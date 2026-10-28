<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/users.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
require_permission('manage_clients', $pdo);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleToCreate = $_POST['role_name'] ?? 'Cliente';
    if ($roleToCreate === 'Empleado' && !has_permission('manage_users', $pdo)) {
        $errors[] = 'No tienes permiso para crear empleados.';
    } else {
        $result = register_account($pdo, [
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role_name' => $roleToCreate,
            'company' => $_POST['company'] ?? '',
            'contact_name' => $_POST['contact_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
        ]);

        if ($result['ok']) {
            $success = 'Usuario registrado correctamente.';
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registrar usuario - Tapimon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/dashboard_nav.php'; ?>
<div class="container my-4">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h3 class="mb-3">Registrar cliente o empleado</h3>
          <?php if ($errors): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($errors[0])?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success"><?=htmlspecialchars($success)?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Tipo de cuenta</label>
              <select name="role_name" class="form-select">
                <option value="Cliente">Cliente</option>
                <?php if (has_permission('manage_users', $pdo)): ?>
                  <option value="Empleado">Empleado</option>
                <?php endif; ?>
              </select>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Usuario</label>
                <input name="username" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Nombre completo</label>
                <input name="full_name" class="form-control" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Correo</label>
                <input name="email" type="email" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Teléfono</label>
                <input name="phone" class="form-control">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Contraseña</label>
                <input name="password" type="password" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Confirmar contraseña</label>
                <input name="confirm_password" type="password" class="form-control" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Empresa / contacto</label>
                <input name="company" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Nombre de contacto</label>
                <input name="contact_name" class="form-control">
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Dirección</label>
              <textarea name="address" class="form-control"></textarea>
            </div>
            <button class="btn btn-success">Registrar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
