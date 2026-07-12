<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$admin_id = (int)$_SESSION['admin_id'];

// ── Registrar acceso manual ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_acceso'])) {
    if (!csrf_verify()) {
        flash('error', 'Token inválido.', 'error');
    } else {
        $cliente_id = (int)($_POST['cliente_id'] ?? 0);
        $tipo       = clean($_POST['tipo']       ?? 'entrada');
        $tipos_validos = ['entrada', 'salida'];

        if ($cliente_id && in_array($tipo, $tipos_validos)) {
            // Verificar cliente existe
            $c = db()->prepare("SELECT id, activo FROM clientes WHERE id = :id LIMIT 1");
            $c->execute([':id' => $cliente_id]);
            $cli = $c->fetch();

            if ($cli && $cli['activo']) {
                // Verificar membresía activa
                $activa = membresia_activa($cliente_id);
                $resultado = $activa ? 'permitido' : 'denegado';
                $motivo    = $activa ? null : 'Sin membresía activa';

                db()->prepare(
                    "INSERT INTO registros_acceso (cliente_id, tipo, metodo, resultado, motivo, admin_id)
                     VALUES (:cid, :tipo, 'manual', :resultado, :motivo, :admin)"
                )->execute([
                    ':cid'       => $cliente_id,
                    ':tipo'      => $tipo,
                    ':resultado' => $resultado,
                    ':motivo'    => $motivo,
                    ':admin'     => $admin_id,
                ]);

                $msg = $resultado === 'permitido'
                    ? ucfirst($tipo) . ' registrada — acceso permitido.'
                    : ucfirst($tipo) . ' registrada — acceso DENEGADO (sin membresía activa).';
                flash('success', $msg, $resultado === 'permitido' ? 'success' : 'error');
            } else {
                flash('error', 'Cliente no encontrado o inactivo.', 'error');
            }
        } else {
            flash('error', 'Selecciona un cliente y tipo válidos.', 'error');
        }
    }
    redirect(APP_URL . '/admin/accesos.php');
}

// ── Filtros ───────────────────────────────────────
$buscar     = clean($_GET['q']          ?? '');
$filtro_tipo= clean($_GET['tipo']       ?? '');
$filtro_res = clean($_GET['resultado']  ?? '');
$fecha      = clean($_GET['fecha']      ?? '');
$cliente_id_filtro = isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;

// Preselect para "Registrar acceso" desde cliente_detalle
$preselect_cliente = isset($_GET['registrar']) && is_numeric($_GET['registrar']) ? (int)$_GET['registrar'] : 0;

$where  = "WHERE 1=1";
$params = [];

if ($buscar) {
    $where .= " AND (cl.nombre LIKE :q OR cl.email LIKE :q)";
    $params[':q'] = "%$buscar%";
}
if ($filtro_tipo)             { $where .= " AND ra.tipo = :tipo";       $params[':tipo'] = $filtro_tipo; }
if ($filtro_res)              { $where .= " AND ra.resultado = :res";   $params[':res']  = $filtro_res; }
if ($cliente_id_filtro)       { $where .= " AND ra.cliente_id = :cid"; $params[':cid']  = $cliente_id_filtro; }
if ($fecha && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $where .= " AND DATE(ra.fecha_hora) = :fecha";
    $params[':fecha'] = $fecha;
}

$accesos = db()->prepare(
    "SELECT ra.*, cl.nombre AS cliente_nombre, cl.email AS cliente_email
     FROM registros_acceso ra
     JOIN clientes cl ON cl.id = ra.cliente_id
     $where
     ORDER BY ra.fecha_hora DESC
     LIMIT 200"
);
$accesos->execute($params);
$lista = $accesos->fetchAll();

// ── Stats de hoy ──────────────────────────────────
$hoy_entradas = db()->query(
    "SELECT COUNT(*) FROM registros_acceso WHERE DATE(fecha_hora) = CURDATE() AND tipo='entrada' AND resultado='permitido'"
)->fetchColumn();

$hoy_denegados = db()->query(
    "SELECT COUNT(*) FROM registros_acceso WHERE DATE(fecha_hora) = CURDATE() AND resultado='denegado'"
)->fetchColumn();

$activos_ahora = db()->query(
    "SELECT COUNT(DISTINCT cliente_id) FROM compras
     WHERE status='completado' AND fecha_fin >= CURDATE()"
)->fetchColumn();

// Lista de clientes activos para el selector
$clientes = db()->query(
    "SELECT id, nombre, email FROM clientes WHERE activo = 1 ORDER BY nombre ASC"
)->fetchAll();

$flash_ok  = get_flash('success');
$flash_err = get_flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Accesos — Admin EgoGym</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
<style>
.acceso-entrada { border-left: 3px solid var(--success); }
.acceso-salida  { border-left: 3px solid var(--tq); }
.acceso-denegado{ border-left: 3px solid var(--danger); }
.badge-info     { background:rgba(56,224,208,.12); color:var(--tq); border:1px solid var(--tq-border); }
</style>
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>
  <div class="main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="content">
      <div class="page-header">
        <h1>Control de Acceso</h1>
        <p>Registro de entradas y salidas · <?= date('d/m/Y') ?></p>
      </div>

      <?php if ($flash_ok): ?>
        <div class="alert alert-<?= strpos($flash_ok['message'], 'DENEGADO') !== false ? 'error' : 'success' ?>" style="margin-bottom:16px">
          <?= h($flash_ok['message']) ?>
        </div>
      <?php endif; ?>
      <?php if ($flash_err): ?>
        <div class="alert alert-error" style="margin-bottom:16px"><?= h($flash_err['message']) ?></div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="stats-grid" style="margin-bottom:24px">
        <div class="stat-card">
          <div class="stat-icon success">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
          </div>
          <div class="stat-body">
            <p class="stat-label">Entradas hoy</p>
            <p class="stat-value"><?= $hoy_entradas ?></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--tq)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <div class="stat-body">
            <p class="stat-label">Miembros activos</p>
            <p class="stat-value"><?= $activos_ahora ?></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon warn">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--warn)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
          </div>
          <div class="stat-body">
            <p class="stat-label">Denegados hoy</p>
            <p class="stat-value"><?= $hoy_denegados ?></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pu">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--pu)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="stat-body">
            <p class="stat-label">Registros mostrados</p>
            <p class="stat-value"><?= count($lista) ?></p>
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

        <!-- Panel de registro manual -->
        <div class="card" style="position:sticky;top:80px">
          <div class="card-header">
            <h3 class="card-title">Registrar acceso</h3>
          </div>
          <div class="card-body">
            <form method="POST" action="" novalidate>
              <?php csrf_field(); ?>
              <input type="hidden" name="registrar_acceso" value="1"/>

              <input type="hidden" name="tipo" value="entrada"/>

              <div class="form-group">
                <label for="cliente_id">Cliente</label>
                <select id="cliente_id" name="cliente_id" class="form-control" required>
                  <option value="">— Selecciona cliente —</option>
                  <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $preselect_cliente === $c['id'] ? 'selected' : '' ?>>
                      <?= h($c['nombre']) ?> — <?= h($c['email']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <button type="submit" class="btn btn-primary btn-full">
                ↓ Registrar entrada
              </button>
            </form>
          </div>
          <div class="card-body" style="border-top:1px solid var(--border);padding-top:14px">
            <p style="font-size:.75rem;color:var(--muted);line-height:1.6">
              El sistema verifica automáticamente si el cliente tiene membresía activa y registra el resultado.
            </p>
            <p style="font-size:.75rem;color:var(--tq);margin-top:8px">
              🔌 API lista para lector de huella → <code style="font-size:.7rem;background:var(--bg3);padding:2px 6px;border-radius:4px">/api/validar_acceso.php</code>
            </p>
          </div>
        </div>

        <!-- Log de accesos -->
        <div>
          <!-- Filtros -->
          <form method="GET" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
            <input type="text" name="q" value="<?= h($buscar) ?>"
                   placeholder="Buscar cliente..." class="form-control" style="width:220px"/>
            <select name="resultado" class="form-control" style="width:140px">
              <option value="">Todos</option>
              <option value="permitido" <?= $filtro_res === 'permitido' ? 'selected' : '' ?>>Permitido</option>
              <option value="denegado"  <?= $filtro_res === 'denegado'  ? 'selected' : '' ?>>Denegado</option>
            </select>
            <input type="date" name="fecha" value="<?= h($fecha) ?>" class="form-control" style="width:155px"/>
            <button type="submit" class="btn btn-ghost">Filtrar</button>
            <?php if ($buscar || $filtro_tipo || $filtro_res || $fecha || $cliente_id_filtro): ?>
              <a href="accesos.php" class="btn btn-danger btn-sm">✕</a>
            <?php endif; ?>
          </form>

          <div class="card">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Fecha / Hora</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Método</th>
                    <th>Resultado</th>
                    <th>Nota</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($lista): foreach ($lista as $a):
                    $row_class = $a['resultado'] === 'denegado' ? 'acceso-denegado' : ($a['tipo'] === 'entrada' ? 'acceso-entrada' : 'acceso-salida');
                  ?>
                  <tr class="<?= $row_class ?>">
                    <td style="font-size:.82rem;color:var(--muted);white-space:nowrap"><?= fmt_fecha_hora($a['fecha_hora']) ?></td>
                    <td>
                      <a href="cliente_detalle.php?id=<?= $a['cliente_id'] ?>" style="font-weight:600;color:var(--white)"><?= h($a['cliente_nombre']) ?></a>
                      <div style="font-size:.76rem;color:var(--muted)"><?= h($a['cliente_email']) ?></div>
                    </td>
                    <td>
                      <span class="badge <?= $a['tipo'] === 'entrada' ? 'badge-success' : 'badge-info' ?>">
                        <?= $a['tipo'] === 'entrada' ? '↓ Entrada' : '↑ Salida' ?>
                      </span>
                    </td>
                    <td style="font-size:.82rem;color:var(--muted)"><?= ucfirst($a['metodo']) ?></td>
                    <td>
                      <span class="badge <?= $a['resultado'] === 'permitido' ? 'badge-success' : 'badge-danger' ?>">
                        <?= ucfirst($a['resultado']) ?>
                      </span>
                    </td>
                    <td style="font-size:.78rem;color:var(--muted)"><?= $a['motivo'] ? h($a['motivo']) : '—' ?></td>
                  </tr>
                  <?php endforeach; else: ?>
                  <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:32px">Sin registros de acceso</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
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

</script>
</body>
</html>
