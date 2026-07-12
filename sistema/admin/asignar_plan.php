<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$cliente_id = isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;

if (!$cliente_id) redirect(APP_URL . '/admin/clientes.php');

// Cargar cliente
$stmt = db()->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $cliente_id]);
$cliente = $stmt->fetch();
if (!$cliente) { flash('error', 'Cliente no encontrado.', 'error'); redirect(APP_URL . '/admin/clientes.php'); }

// Membresías activas
$membresias = db()->query("SELECT * FROM membresias WHERE activa = 1 ORDER BY precio ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Token inválido.';
    } else {
        $membresia_id  = (int)($_POST['membresia_id']  ?? 0);
        $metodo_pago   = clean($_POST['metodo_pago']   ?? '');
        $notas         = clean($_POST['notas']         ?? '');
        $fecha_inicio  = clean($_POST['fecha_inicio']  ?? '');
        $metodos_validos = ['efectivo', 'tarjeta_presencial', 'cortesia'];

        // Validar membresía
        $stmt2 = db()->prepare("SELECT * FROM membresias WHERE id = :id AND activa = 1 LIMIT 1");
        $stmt2->execute([':id' => $membresia_id]);
        $memb = $stmt2->fetch();

        if (!$memb)                              $errors[] = 'Selecciona una membresía válida.';
        if (!in_array($metodo_pago, $metodos_validos)) $errors[] = 'Método de pago inválido.';
        if (!$fecha_inicio || !strtotime($fecha_inicio)) $errors[] = 'Fecha de inicio inválida.';

        if (empty($errors)) {
            $fin = date('Y-m-d', strtotime($fecha_inicio . ' +' . $memb['duracion_dias'] . ' days'));

            db()->prepare(
                "INSERT INTO compras
                   (cliente_id, membresia_id, monto, metodo_pago, fecha_pago, fecha_inicio, fecha_fin, status, correo_enviado, notas)
                 VALUES
                   (:cid, :mid, :monto, :metodo, NOW(), :fi, :ff, 'completado', 0, :notas)"
            )->execute([
                ':cid'    => $cliente_id,
                ':mid'    => $memb['id'],
                ':monto'  => $memb['precio'],
                ':metodo' => $metodo_pago,
                ':fi'     => $fecha_inicio,
                ':ff'     => $fin,
                ':notas'  => $notas ?: null,
            ]);

            flash('success', 'Plan "' . $memb['nombre'] . '" asignado correctamente.', 'success');
            redirect(APP_URL . '/admin/cliente_detalle.php?id=' . $cliente_id);
        }
    }
}

$hoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Asignar plan — Admin EgoGym</title>
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
      <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px">
        <div>
          <h1>Asignar plan</h1>
          <p>Cliente: <strong style="color:var(--white)"><?= h($cliente['nombre']) ?></strong> · <?= h($cliente['email']) ?></p>
        </div>
        <a href="cliente_detalle.php?id=<?= $cliente_id ?>" class="btn btn-ghost">← Volver</a>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom:20px">
          <?php foreach ($errors as $e): echo h($e) . '<br>'; endforeach; ?>
        </div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

        <!-- Formulario -->
        <div class="card">
          <div class="card-header"><h3 class="card-title">Datos del plan</h3></div>
          <div class="card-body">
            <form method="POST" action="" novalidate id="form-asignar">
              <?php csrf_field(); ?>

              <div class="form-group">
                <label for="membresia_id">Membresía</label>
                <select id="membresia_id" name="membresia_id" class="form-control" required onchange="actualizarResumen()">
                  <option value="">— Selecciona —</option>
                  <?php foreach ($membresias as $m): ?>
                    <option value="<?= $m['id'] ?>"
                      data-precio="<?= $m['precio'] ?>"
                      data-dias="<?= $m['duracion_dias'] ?>"
                      data-nombre="<?= h($m['nombre']) ?>">
                      <?= h($m['nombre']) ?> — <?= fmt_precio((float)$m['precio']) ?> (<?= $m['duracion_dias'] ?> días)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="metodo_pago">Método de pago</label>
                <select id="metodo_pago" name="metodo_pago" class="form-control" required>
                  <option value="">— Selecciona —</option>
                  <option value="efectivo">Efectivo</option>
                  <option value="tarjeta_presencial">Tarjeta presencial</option>
                  <option value="cortesia">Cortesía / Sin costo</option>
                </select>
              </div>

              <div class="form-group">
                <label for="fecha_inicio">Fecha de inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control"
                       value="<?= $hoy ?>" required onchange="actualizarResumen()"/>
              </div>

              <div class="form-group">
                <label for="notas">Notas internas <span style="color:var(--muted);font-weight:400">(opcional)</span></label>
                <textarea id="notas" name="notas" class="form-control" rows="2"
                          placeholder="Ej: Pagó con billete de 500, cambio dado..."></textarea>
              </div>

              <button type="submit" class="btn btn-primary btn-full">Asignar plan</button>
            </form>
          </div>
        </div>

        <!-- Resumen -->
        <div class="card" id="resumen-card" style="display:none">
          <div class="card-header"><h3 class="card-title">Resumen</h3></div>
          <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)">
              <span style="color:var(--muted);font-size:.85rem">Cliente</span>
              <span style="font-weight:600;color:var(--white)"><?= h($cliente['nombre']) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)">
              <span style="color:var(--muted);font-size:.85rem">Plan</span>
              <span id="res-plan" style="font-weight:600;color:var(--white)">—</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)">
              <span style="color:var(--muted);font-size:.85rem">Monto</span>
              <span id="res-monto" style="font-weight:700;color:var(--tq);font-size:1.1rem">—</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)">
              <span style="color:var(--muted);font-size:.85rem">Inicio</span>
              <span id="res-inicio" style="font-weight:600">—</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0">
              <span style="color:var(--muted);font-size:.85rem">Vencimiento</span>
              <span id="res-fin" style="font-weight:600;color:var(--success)">—</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('open');}

function actualizarResumen() {
  const sel = document.getElementById('membresia_id');
  const opt = sel.options[sel.selectedIndex];
  const inicio = document.getElementById('fecha_inicio').value;
  const card = document.getElementById('resumen-card');

  if (!opt.value || !inicio) { card.style.display = 'none'; return; }

  const dias  = parseInt(opt.dataset.dias);
  const precio = parseFloat(opt.dataset.precio);
  const fechaInicio = new Date(inicio + 'T00:00:00');
  const fechaFin = new Date(fechaInicio);
  fechaFin.setDate(fechaFin.getDate() + dias);

  document.getElementById('res-plan').textContent  = opt.dataset.nombre;
  document.getElementById('res-monto').textContent = '$' + precio.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' MXN';
  document.getElementById('res-inicio').textContent = fechaInicio.toLocaleDateString('es-MX');
  document.getElementById('res-fin').textContent   = fechaFin.toLocaleDateString('es-MX');
  card.style.display = 'block';
}
</script>
</body>
</html>
