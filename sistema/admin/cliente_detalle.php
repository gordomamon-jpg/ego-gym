<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect(APP_URL . '/admin/clientes.php');

// Cargar cliente
$stmt = db()->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$cliente = $stmt->fetch();
if (!$cliente) { flash('error', 'Cliente no encontrado.', 'error'); redirect(APP_URL . '/admin/clientes.php'); }

// Toggle activo
if (isset($_GET['toggle_activo']) && isset($_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    db()->prepare("UPDATE clientes SET activo = NOT activo WHERE id = :id")->execute([':id' => $id]);
    flash('success', 'Estado del cliente actualizado.', 'success');
    redirect(APP_URL . '/admin/cliente_detalle.php?id=' . $id);
}

// Huella dactilar
$stmt_h = db()->prepare("SELECT COUNT(*) FROM huellas WHERE cliente_id = :id AND activa = 1");
$stmt_h->execute([':id' => $id]);
$tiene_huella = (bool)$stmt_h->fetchColumn();

// Membresía activa
$activa = membresia_activa($id);
$dias_restantes = 0;
if ($activa) {
    $hoy = new DateTime('today');
    $fin = new DateTime($activa['fecha_fin']);
    $dias_restantes = max(0, (int)$hoy->diff($fin)->days);
}

// Historial de compras
$compras = db()->prepare(
    "SELECT c.*, m.nombre AS membresia_nombre
     FROM compras c JOIN membresias m ON m.id = c.membresia_id
     WHERE c.cliente_id = :id ORDER BY c.created_at DESC LIMIT 20"
);
$compras->execute([':id' => $id]);
$historial_compras = $compras->fetchAll();

// Últimos accesos
$accesos = db()->prepare(
    "SELECT * FROM registros_acceso
     WHERE cliente_id = :id ORDER BY fecha_hora DESC LIMIT 15"
);
$accesos->execute([':id' => $id]);
$historial_accesos = $accesos->fetchAll();

$flash_ok  = get_flash('success');
$flash_err = get_flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?= h($cliente['nombre']) ?> — Admin EgoGym</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>
  <div class="main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="content">

      <?php if ($flash_ok): ?>
        <div class="alert alert-success" style="margin-bottom:20px"><?= h($flash_ok['message']) ?></div>
      <?php endif; ?>
      <?php if ($flash_err): ?>
        <div class="alert alert-error" style="margin-bottom:20px"><?= h($flash_err['message']) ?></div>
      <?php endif; ?>

      <!-- Header cliente -->
      <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:20px">
        <div style="display:flex;align-items:center;gap:16px">
          <div style="width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--tq),var(--pu));display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-weight:900;font-size:1.4rem;color:var(--bg);flex-shrink:0">
            <?= strtoupper(substr($cliente['nombre'], 0, 1)) ?>
          </div>
          <div>
            <h1 style="margin-bottom:2px"><?= h($cliente['nombre']) ?></h1>
            <p><?= h($cliente['email']) ?><?= $cliente['telefono'] ? ' · ' . h($cliente['telefono']) : '' ?></p>
            <div style="display:flex;gap:8px;margin-top:6px">
              <span class="badge <?= $cliente['activo'] ? 'badge-success' : 'badge-danger' ?>">
                <?= $cliente['activo'] ? 'Activo' : 'Inactivo' ?>
              </span>
              <?php if ($activa): ?>
                <span class="badge badge-pu"><?= h($activa['membresia_nombre']) ?></span>
              <?php else: ?>
                <span class="badge badge-muted">Sin membresía</span>
              <?php endif; ?>
              <?php if ($tiene_huella): ?>
                <span class="badge badge-success">Huella ✓</span>
              <?php else: ?>
                <span class="badge badge-warn">Sin huella</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <a href="cliente_form.php?id=<?= $id ?>" class="btn btn-ghost btn-sm">Editar datos</a>
          <a href="asignar_plan.php?cliente_id=<?= $id ?>" class="btn btn-primary btn-sm">+ Asignar plan</a>
          <a href="registrar_huella.php?cliente_id=<?= $id ?>" class="btn btn-sm <?= $tiene_huella ? 'btn-ghost' : 'btn-outline' ?>" title="<?= $tiene_huella ? 'Huella registrada' : 'Sin huella — registrar' ?>">
            <?= $tiene_huella ? '👆 Huella ✓' : '👆 Registrar huella' ?>
          </a>
          <a href="accesos.php?registrar=<?= $id ?>" class="btn btn-ghost btn-sm">Registrar acceso</a>
          <a href="cliente_detalle.php?id=<?= $id ?>&toggle_activo=1&csrf=<?= h(csrf_token()) ?>"
             class="btn btn-sm <?= $cliente['activo'] ? 'btn-danger' : 'btn-ghost' ?>"
             onclick="return confirm('¿Cambiar estado del cliente?')">
            <?= $cliente['activo'] ? 'Desactivar' : 'Activar' ?>
          </a>
          <a href="clientes.php" class="btn btn-ghost btn-sm">← Clientes</a>
        </div>
      </div>

      <!-- Membresía activa -->
      <?php if ($activa): ?>
      <div class="card" style="margin-bottom:20px;border-color:var(--tq-border);background:linear-gradient(135deg,rgba(56,224,208,.06),rgba(122,60,255,.04))">
        <div class="card-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:20px;padding:20px">
          <div>
            <p style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Membresía activa</p>
            <p style="font-family:var(--fh);font-size:1.2rem;font-weight:800;color:var(--tq)"><?= h($activa['membresia_nombre']) ?></p>
          </div>
          <div>
            <p style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Días restantes</p>
            <p style="font-family:var(--fh);font-size:1.5rem;font-weight:900;color:var(--white)"><?= $dias_restantes ?></p>
          </div>
          <div>
            <p style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Inicio</p>
            <p style="font-weight:600"><?= fmt_fecha($activa['fecha_inicio']) ?></p>
          </div>
          <div>
            <p style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Vencimiento</p>
            <p style="font-weight:600;color:<?= $dias_restantes <= 5 ? 'var(--warn)' : 'var(--success)' ?>"><?= fmt_fecha($activa['fecha_fin']) ?></p>
          </div>
          <div>
            <p style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Pagado</p>
            <p style="font-weight:700;color:var(--tq)"><?= fmt_precio((float)$activa['monto']) ?></p>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="card" style="margin-bottom:20px">
        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:20px">
          <p style="color:var(--muted)">Este cliente no tiene membresía activa.</p>
          <a href="asignar_plan.php?cliente_id=<?= $id ?>" class="btn btn-primary btn-sm">+ Asignar plan ahora</a>
        </div>
      </div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        <!-- Historial de accesos -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Historial de accesos</h3>
            <a href="accesos.php?cliente_id=<?= $id ?>" class="btn btn-ghost btn-sm">Ver todos</a>
          </div>
          <?php if ($historial_accesos): ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Fecha/Hora</th><th>Tipo</th><th>Método</th><th>Resultado</th></tr>
              </thead>
              <tbody>
                <?php foreach ($historial_accesos as $a): ?>
                <tr>
                  <td style="font-size:.82rem;color:var(--muted)"><?= fmt_fecha_hora($a['fecha_hora']) ?></td>
                  <td>
                    <span class="badge <?= $a['tipo'] === 'entrada' ? 'badge-success' : 'badge-info' ?>">
                      <?= $a['tipo'] === 'entrada' ? '↓ Entrada' : '↑ Salida' ?>
                    </span>
                  </td>
                  <td style="font-size:.8rem;color:var(--muted)"><?= ucfirst($a['metodo']) ?></td>
                  <td>
                    <span class="badge <?= $a['resultado'] === 'permitido' ? 'badge-success' : 'badge-danger' ?>">
                      <?= ucfirst($a['resultado']) ?>
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <div class="card-body"><p style="color:var(--muted);text-align:center;padding:20px">Sin accesos registrados.</p></div>
          <?php endif; ?>
        </div>

        <!-- Historial de compras -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Historial de compras</h3>
            <a href="asignar_plan.php?cliente_id=<?= $id ?>" class="btn btn-primary btn-sm">+ Asignar</a>
          </div>
          <?php if ($historial_compras): ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Plan</th><th>Monto</th><th>Método</th><th>Vence</th><th>Estado</th></tr>
              </thead>
              <tbody>
                <?php foreach ($historial_compras as $c): ?>
                <?php $badges = ['completado'=>'badge-success','pendiente'=>'badge-warn','fallido'=>'badge-danger','reembolsado'=>'badge-muted']; ?>
                <tr>
                  <td style="font-weight:600;color:var(--white)"><?= h($c['membresia_nombre']) ?></td>
                  <td style="color:var(--tq);font-weight:600"><?= fmt_precio((float)$c['monto']) ?></td>
                  <td style="font-size:.78rem;color:var(--muted)"><?= ucfirst(str_replace('_',' ',$c['metodo_pago'] ?? 'stripe')) ?></td>
                  <td style="font-size:.82rem"><?= fmt_fecha($c['fecha_fin']) ?></td>
                  <td><span class="badge <?= $badges[$c['status']] ?? 'badge-muted' ?>"><?= ucfirst($c['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <div class="card-body"><p style="color:var(--muted);text-align:center;padding:20px">Sin compras registradas.</p></div>
          <?php endif; ?>
        </div>

      </div>

      <!-- Registro cliente -->
      <div style="margin-top:12px;color:var(--muted);font-size:.78rem;text-align:right">
        Registrado: <?= fmt_fecha_hora($cliente['created_at']) ?> · ID #<?= $cliente['id'] ?>
      </div>

    </div>
  </div>
</div>
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('open');}
</script>
</body>
</html>
