<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$cliente_id     = (int) $_SESSION['cliente_id'];
$cliente_nombre = $_SESSION['cliente_nombre'];
$cliente_email  = $_SESSION['cliente_email'];

// Membresía activa
$activa = membresia_activa($cliente_id);

// Días restantes
$dias_restantes = 0;
$porcentaje     = 0;
if ($activa) {
    $hoy       = new DateTime('today');
    $fin       = new DateTime($activa['fecha_fin']);
    $inicio    = new DateTime($activa['fecha_inicio']);
    $total     = (int) $inicio->diff($fin)->days;
    $dias_restantes = max(0, (int) $hoy->diff($fin)->days);
    $porcentaje = $total > 0 ? round(($dias_restantes / $total) * 100) : 0;
}

// Historial de compras
$stmt = db()->prepare(
    "SELECT c.*, m.nombre AS membresia_nombre
     FROM compras c
     JOIN membresias m ON m.id = c.membresia_id
     WHERE c.cliente_id = :id
     ORDER BY c.created_at DESC
     LIMIT 10"
);
$stmt->execute([':id' => $cliente_id]);
$compras = $stmt->fetchAll();

$flash_ok  = get_flash('success');
$flash_err = get_flash('error');

// Verificar si tiene huella registrada
$stmt_h = db()->prepare("SELECT COUNT(*) FROM huellas WHERE cliente_id = :id AND activa = 1");
$stmt_h->execute([':id' => $cliente_id]);
$tiene_huella = (bool)$stmt_h->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Mi cuenta — EgoGym Fitness</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
</head>
<body>
<div class="shell">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <a href="../../index.php"><em>EGOGYM</em> FITNESS</a>
      <span>Panel cliente</span>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="active">
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Mi cuenta
      </a>
      <a href="membresias.php">
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18M7 3v18m10-18v18M3 7a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4H7a4 4 0 01-4-4V7z"/></svg>
        Membresías
      </a>
      <a href="perfil.php">
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Mi perfil
      </a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php">
        <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        Cerrar sesión
      </a>
    </div>
  </aside>
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>

  <!-- MAIN -->
  <div class="main">
    <header class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Menú">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <span class="topbar-title">Mi cuenta</span>
      </div>
      <div class="topbar-user">
        <span><?= h($cliente_nombre) ?></span>
        <div class="topbar-avatar"><?= strtoupper(substr($cliente_nombre, 0, 1)) ?></div>
      </div>
    </header>

    <div class="content">
      <?php if ($flash_ok): ?>
        <div class="alert alert-success"><?= h($flash_ok['message']) ?></div>
      <?php endif; ?>
      <?php if ($flash_err): ?>
        <div class="alert alert-error"><?= h($flash_err['message']) ?></div>
      <?php endif; ?>

      <?php if (!$tiene_huella): ?>
      <div style="background:rgba(255,180,67,.07);border:1px solid rgba(255,180,67,.28);border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:12px">
          <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--warn)" stroke-width="2" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <div>
            <p style="font-weight:600;font-size:.9rem;color:var(--warn)">Huella dactilar no registrada</p>
            <p style="font-size:.82rem;color:var(--muted);margin-top:2px">Acude al gym para que el staff registre tu huella y puedas acceder a las instalaciones.</p>
          </div>
        </div>
        <span style="font-size:.78rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--warn);white-space:nowrap;background:rgba(255,180,67,.1);padding:6px 14px;border-radius:20px;border:1px solid rgba(255,180,67,.25)">Pendiente</span>
      </div>
      <?php endif; ?>

      <div class="page-header">
        <h1>Hola, <?= h(explode(' ', $cliente_nombre)[0]) ?></h1>
        <p><?= h($cliente_email) ?></p>
      </div>

      <?php if ($activa): ?>
        <!-- Membresía activa -->
        <div class="dash-grid">
          <div class="membresia-activa-card">
            <p class="ma-label">Membresía activa</p>
            <h2 class="ma-nombre"><?= h($activa['membresia_nombre']) ?></h2>
            <div class="ma-fechas">
              <div class="ma-fecha-item">
                <span>Inicio</span>
                <?= fmt_fecha($activa['fecha_inicio']) ?>
              </div>
              <div class="ma-fecha-item">
                <span>Vencimiento</span>
                <?= fmt_fecha($activa['fecha_fin']) ?>
              </div>
              <div class="ma-fecha-item">
                <span>Pagado</span>
                <?= fmt_precio((float)$activa['monto']) ?>
              </div>
            </div>
            <div class="progress-wrap">
              <div class="progress-label">
                <span>Tiempo restante</span>
                <span><?= $porcentaje ?>%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-fill" style="width:<?= $porcentaje ?>%"></div>
              </div>
            </div>
          </div>

          <div class="dias-restantes">
            <div class="dias-numero"><?= $dias_restantes ?></div>
            <div class="dias-label">días restantes</div>
            <a href="membresias.php" class="btn btn-outline" style="margin-top:16px">Renovar membresía</a>
          </div>
        </div>
      <?php else: ?>
        <!-- Sin membresía -->
        <div class="card" style="margin-bottom:24px">
          <div class="empty-state">
            <div class="icon-circle">
              <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="var(--tq)" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <h3>Sin membresía activa</h3>
            <p>Elige un plan y empieza a entrenar hoy mismo sin costo de inscripción.</p>
            <a href="membresias.php" class="btn btn-primary btn-lg">Ver membresías</a>
          </div>
        </div>
      <?php endif; ?>

      <!-- Historial -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Historial de compras</h3>
        </div>
        <?php if ($compras): ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Membresía</th>
                  <th>Monto</th>
                  <th>Inicio</th>
                  <th>Vencimiento</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($compras as $c): ?>
                  <tr class="compra-row">
                    <td><?= $c['id'] ?></td>
                    <td><?= h($c['membresia_nombre']) ?></td>
                    <td><?= fmt_precio((float)$c['monto']) ?></td>
                    <td><?= fmt_fecha($c['fecha_inicio']) ?></td>
                    <td><?= fmt_fecha($c['fecha_fin']) ?></td>
                    <td>
                      <?php
                      $badges = [
                        'completado' => 'badge-success',
                        'pendiente'  => 'badge-warn',
                        'fallido'    => 'badge-danger',
                        'reembolsado'=> 'badge-muted',
                      ];
                      $b = $badges[$c['status']] ?? 'badge-muted';
                      ?>
                      <span class="badge <?= $b ?>"><?= h(ucfirst($c['status'])) ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="card-body">
            <p style="color:var(--muted);text-align:center;padding:24px 0">Aún no tienes compras registradas.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('open');
}
</script>
</body>
</html>
