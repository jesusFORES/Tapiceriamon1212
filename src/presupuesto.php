<?php
/**
 * Lógica de negocio para cálculos de presupuestos
 */

function precio_por_mueble($tipo) {
    $map = [
        'Sofá 3 plazas' => 200,
        'Sillón' => 120,
        'Silla de comedor' => 40,
        'Sofá 2 plazas' => 160,
    ];
    return $map[$tipo] ?? 100;
}

function precio_tela_por_metro($tipo_tela) {
    $map = [
        'Tela' => 20.0,
        'Semicuero' => 35.0,
        'Otro' => 18.0,
    ];
    return $map[$tipo_tela] ?? 20.0;
}

function multiplicador_complejidad($nivel) {
    $map = [1 => 1.0, 2 => 1.2, 3 => 1.5];
    return $map[$nivel] ?? 1.0;
}

function precio_espuma($tipo_espuma) {
    $map = [
        'Básica' => 10.0,
        'Alta densidad' => 30.0,
    ];
    return $map[$tipo_espuma] ?? 15.0;
}

function calcular_item($mueble_tipo, $metros_tela, $tipo_tela, $complejidad, $tipo_espuma, $cantidad=1) {
    $base_mueble = precio_por_mueble($mueble_tipo);
    $precio_tela = precio_tela_por_metro($tipo_tela) * $metros_tela;
    $espuma = precio_espuma($tipo_espuma);
    $mano_obra = ($base_mueble + $precio_tela + $espuma) * multiplicador_complejidad($complejidad);
    $subtotal = ($mano_obra) * $cantidad;
    return [
        'concepto' => "$mueble_tipo - Tela $tipo_tela - Espuma $tipo_espuma",
        'cantidad' => $cantidad,
        'precio_unit' => round($mano_obra,2),
        'subtotal' => round($subtotal,2)
    ];
}

function ensure_presupuesto_financial_columns($pdo) {
    $columns = $pdo->query("SHOW COLUMNS FROM presupuestos")->fetchAll(PDO::FETCH_COLUMN, 0);
    if (!in_array('monto_bs', $columns, true)) {
        $pdo->exec('ALTER TABLE presupuestos ADD COLUMN monto_bs DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER total');
    }
    if (!in_array('abono_bs', $columns, true)) {
        $pdo->exec('ALTER TABLE presupuestos ADD COLUMN abono_bs DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER monto_bs');
    }
    if (!in_array('saldo_restante_bs', $columns, true)) {
        $pdo->exec('ALTER TABLE presupuestos ADD COLUMN saldo_restante_bs DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER abono_bs');
    }
}

function crear_presupuesto($pdo, $cliente_id, $empleado_id, $items, $nota = null, $monto_bs = 0, $abono_bs = 0) {
    try {
        ensure_presupuesto_financial_columns($pdo);
        $pdo->beginTransaction();
        $total = 0.0;
        foreach ($items as $it) {
            $total += $it['subtotal'];
        }
        $monto_bs = floatval($monto_bs);
        $abono_bs = floatval($abono_bs);
        $saldo_restante_bs = max(0, $monto_bs - $abono_bs);
        $stmt = $pdo->prepare("INSERT INTO presupuestos (cliente_id, empleado_id, total, monto_bs, abono_bs, saldo_restante_bs, estado, nota, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Creado', ?, NOW())");
        $stmt->execute([$cliente_id, $empleado_id, $total, $monto_bs, $abono_bs, $saldo_restante_bs, $nota]);
        $presupuesto_id = $pdo->lastInsertId();
        $stmtItem = $pdo->prepare("INSERT INTO presupuesto_items (presupuesto_id, mueble_id, concepto, cantidad, precio_unit, subtotal) VALUES (?, NULL, ?, ?, ?, ?)");
        foreach ($items as $it) {
            $stmtItem->execute([$presupuesto_id, $it['concepto'], $it['cantidad'], $it['precio_unit'], $it['subtotal']]);
        }
        // Insert initial estado historial
        $stmtHist = $pdo->prepare("INSERT INTO presupuesto_estado_historial (presupuesto_id, estado, comentario, changed_by, changed_at) VALUES (?, 'Creado', ?, ?, NOW())");
        $stmtHist->execute([$presupuesto_id, 'Presupuesto creado', $empleado_id]);
        $pdo->commit();
        return $presupuesto_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function delete_presupuesto_by_id($pdo, $presupuestoId, $deletedById) {
    try {
        $stmt = $pdo->prepare('SELECT id FROM presupuestos WHERE id = ?');
        $stmt->execute([$presupuestoId]);
        if (!$stmt->fetch()) {
            return ['ok' => false, 'message' => 'Presupuesto no encontrado.'];
        }
        $stmt = $pdo->prepare('DELETE FROM presupuestos WHERE id = ?');
        $stmt->execute([$presupuestoId]);
        return ['ok' => true, 'message' => 'Presupuesto eliminado correctamente.'];
    } catch (Exception $e) {
        return ['ok' => false, 'message' => 'Error al eliminar presupuesto: ' . $e->getMessage()];
    }
}

?>
