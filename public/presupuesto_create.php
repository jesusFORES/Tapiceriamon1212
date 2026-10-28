<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/presupuesto.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
require_permission('create_presupuestos', $pdo);

$clients = get_clients($pdo);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = intval($_POST['cliente_id'] ?? 0);
    $mueble_tipo = $_POST['mueble_tipo'] ?? 'Sofá 3 plazas';
    $metros = floatval($_POST['metros_tela'] ?? 0);
    $tipo_tela = $_POST['tipo_tela'] ?? 'Tela';
    $complejidad = intval($_POST['complejidad'] ?? 1);
    $tipo_espuma = $_POST['tipo_espuma'] ?? 'Básica';
    $cantidad = intval($_POST['cantidad'] ?? 1);
    $monto_bs = floatval($_POST['monto_bs'] ?? 0);
    $abono_bs = floatval($_POST['abono_bs'] ?? 0);
    $saldo_restante_bs = max(0, $monto_bs - $abono_bs);
    $nota = $_POST['nota'] ?? null;

    $item = calcular_item($mueble_tipo, $metros, $tipo_tela, $complejidad, $tipo_espuma, $cantidad);
    try {
        $pres_id = crear_presupuesto($pdo, $cliente_id, $user['id'], [$item], $nota, $monto_bs, $abono_bs);
        header('Location: /presupuestos.php');
        exit;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Crear Presupuesto - Tapimon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/dashboard_nav.php'; ?>
<div class="container my-4">
  <h3>Crear Presupuesto</h3>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><?=htmlspecialchars($errors[0])?></div>
  <?php endif; ?>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">Cliente</label>
      <select name="cliente_id" class="form-select" required>
        <?php foreach ($clients as $c): ?>
          <option value="<?=e($c['id'])?>"><?=e($c['company'] ?: $c['contact_name'])?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Tipo de mueble</label>
      <select name="mueble_tipo" class="form-select">
        <option>Sofá 3 plazas</option>
        <option>Sofá 2 plazas</option>
        <option>Sillón</option>
        <option>Silla de comedor</option>
      </select>
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Metros de tela</label>
        <input name="metros_tela" type="number" step="0.1" class="form-control" value="2">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Tipo de tela</label>
        <select name="tipo_tela" class="form-select">
          <option>Tela</option>
          <option>Semicuero</option>
          <option>Otro</option>
        </select>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Complejidad</label>
        <select name="complejidad" class="form-select">
          <option value="1">1 - Básica</option>
          <option value="2">2 - Media</option>
          <option value="3">3 - Alta</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label">Monto total (Bs.)</label>
        <input id="monto_bs" name="monto_bs" type="number" min="0" step="0.01" class="form-control" value="0">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Abono (Bs.)</label>
        <input id="abono_bs" name="abono_bs" type="number" min="0" step="0.01" class="form-control" value="0">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label">Resta por pagar (Bs.)</label>
        <input id="saldo_restante_bs" type="text" class="form-control" value="0,00" readonly>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label">Tipo de espuma</label>
      <select name="tipo_espuma" class="form-select">
        <option>Básica</option>
        <option>Alta densidad</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Cantidad</label>
      <input name="cantidad" type="number" class="form-control" value="1">
    </div>
    <div class="mb-3">
      <label class="form-label">Nota</label>
      <textarea name="nota" class="form-control"></textarea>
    </div>
    <div class="d-grid">
      <button class="btn btn-success">Crear Presupuesto</button>
    </div>
  </form>
</div>
<script>
  const montoInput = document.getElementById('monto_bs');
  const abonoInput = document.getElementById('abono_bs');
  const saldoInput = document.getElementById('saldo_restante_bs');

  function formatBs(value) {
    return Number(value || 0).toLocaleString('es-VE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function actualizarSaldo() {
    const monto = Number(montoInput.value || 0);
    const abono = Number(abonoInput.value || 0);
    const restante = Math.max(0, monto - abono);
    saldoInput.value = formatBs(restante);
  }

  montoInput.addEventListener('input', actualizarSaldo);
  abonoInput.addEventListener('input', actualizarSaldo);
</script>
</body>
</html>
