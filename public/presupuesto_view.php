<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
require __DIR__ . '/../src/presupuesto_chat.php';
start_secure_session();
require_login();
$pdo = require __DIR__ . '/../src/db.php';
$user = current_user();
if ($user['role'] === 'Cliente') {
    require_permission('view_own_presupuestos', $pdo);
} else {
    require_permission('view_presupuestos', $pdo);
}
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.contact_name, c.company FROM presupuestos p JOIN clients c ON p.cliente_id = c.id WHERE p.id = ? LIMIT 1");
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { echo 'No encontrado'; exit; }
if ($user['role'] === 'Cliente') {
    $stmt2 = $pdo->prepare("SELECT COUNT(*) as c FROM clients WHERE id = ? AND user_id = ?");
    $stmt2->execute([$p['cliente_id'], $user['id']]);
    $c = $stmt2->fetch();
    if (empty($c['c'])) { http_response_code(403); echo 'Acceso denegado'; exit; }
} elseif ($user['role'] === 'Empleado') {
    $stmt2 = $pdo->prepare("SELECT COUNT(*) as c FROM presupuestos WHERE id = ? AND empleado_id = ?");
    $stmt2->execute([$id, $user['id']]);
    $c = $stmt2->fetch();
    if (empty($c['c'])) { http_response_code(403); echo 'Acceso denegado'; exit; }
}
$items = $pdo->prepare("SELECT * FROM presupuesto_items WHERE presupuesto_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();
$hist = $pdo->prepare("SELECT * FROM presupuesto_estado_historial WHERE presupuesto_id = ? ORDER BY changed_at DESC");
$hist->execute([$id]);
$hist = $hist->fetchAll();
$chatMessages = get_chat_messages($pdo, $id);
$messageError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $messageText = trim((string)($_POST['message'] ?? ''));
    $attachmentPath = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['attachment']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed, true)) {
            $messageError = 'Solo se permiten imágenes JPG, PNG, GIF o WEBP.';
        } else {
            $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $filename = 'budget-' . $id . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
            $target = __DIR__ . '/uploads/' . $filename;
            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
                $messageError = 'No se pudo guardar la imagen.';
            } else {
                $attachmentPath = 'uploads/' . $filename;
            }
        }
    }
    if ($messageError === '') {
        $result = save_chat_message($pdo, $id, $user, $messageText, $attachmentPath);
        if ($result['ok']) {
            header('Location: /presupuesto_view.php?id=' . $id);
            exit;
        }
        $messageError = $result['message'];
    }
    $chatMessages = get_chat_messages($pdo, $id);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ver Presupuesto #<?=$p['id']?> - Tapimon</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/dashboard_nav.php'; ?>
<div class="container my-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Presupuesto #<?=htmlspecialchars($p['id'])?></h3>
    <?php if (has_permission('delete_presupuestos')): ?>
      <form method="post" action="/delete_presupuesto.php" onsubmit="return confirm('¿Eliminar este presupuesto?');" class="mb-0">
        <input type="hidden" name="presupuesto_id" value="<?=htmlspecialchars($p['id'])?>">
        <button class="btn btn-sm btn-danger">Eliminar presupuesto</button>
      </form>
    <?php endif; ?>
  </div>
  <p><strong>Cliente:</strong> <?=htmlspecialchars($p['company'] ?: $p['contact_name'])?></p>
  <p><strong>Estado:</strong> <?=htmlspecialchars($p['estado'])?></p>
  <p><strong>Monto total:</strong> <?=number_format((float)($p['monto_bs'] ?? $p['total']), 2, ',', '.') ?> Bs.</p>
  <p><strong>Abono:</strong> <?=number_format((float)($p['abono_bs'] ?? 0), 2, ',', '.') ?> Bs.</p>
  <p><strong>Resta por pagar:</strong> <?=number_format((float)($p['saldo_restante_bs'] ?? max(0, ((float)($p['monto_bs'] ?? $p['total'])) - (float)($p['abono_bs'] ?? 0))), 2, ',', '.') ?> Bs.</p>
  <div class="row">
    <div class="col-lg-7">
      <table class="table">
        <thead><tr><th>Concepto</th><th>Cantidad</th><th>Precio unit</th><th>Subtotal</th></tr></thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?=htmlspecialchars($it['concepto'])?></td>
              <td><?=htmlspecialchars($it['cantidad'])?></td>
              <td><?=number_format($it['precio_unit'],2)?></td>
              <td><?=number_format($it['subtotal'],2)?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><th colspan="3">Total</th><th><?=number_format($p['total'],2)?></th></tr>
        </tfoot>
      </table>
      <h5>Historial de estados</h5>
      <ul>
        <?php foreach ($hist as $h): ?>
          <li><?=htmlspecialchars($h['changed_at'])?> - <?=htmlspecialchars($h['estado'])?> - <?=htmlspecialchars($h['comentario'])?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Chat del presupuesto</h5>
          <p class="text-muted">Cliente, empleado o administrador pueden conversar y adjuntar una imagen de referencia.</p>
          <?php if ($messageError !== ''): ?>
            <div class="alert alert-danger"><?=htmlspecialchars($messageError)?></div>
          <?php endif; ?>
          <div class="border rounded p-3 mb-3" style="max-height: 320px; overflow:auto;">
            <?php if ($chatMessages): ?>
              <?php foreach ($chatMessages as $msg): ?>
                <div class="mb-3">
                  <div class="fw-bold"><?=htmlspecialchars($msg['sender_name'])?> <small class="text-muted">(<?=htmlspecialchars($msg['sender_role'])?>)</small></div>
                  <?php if (!empty($msg['message'])): ?><div><?=nl2br(htmlspecialchars($msg['message']))?></div><?php endif; ?>
                  <?php if (!empty($msg['attachment_path'])): ?><div class="mt-2"><img src="/<?=htmlspecialchars($msg['attachment_path'])?>" class="img-fluid rounded" style="max-height:220px;"></div><?php endif; ?>
                  <div class="small text-muted"><?=htmlspecialchars($msg['created_at'])?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted">Todavía no hay mensajes. Sube una imagen o escribe una consulta.</div>
            <?php endif; ?>
          </div>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="send_message" value="1">
            <div class="mb-2">
              <textarea name="message" class="form-control" rows="3" placeholder="Escribe una respuesta o una duda sobre este presupuesto"></textarea>
            </div>
            <div class="mb-2">
              <input type="file" name="attachment" class="form-control" accept="image/*">
            </div>
            <button class="btn btn-primary">Enviar al equipo</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
