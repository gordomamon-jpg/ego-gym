<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$admin_nombre = $_SESSION['admin_nombre'];

// Stats
$stats = [];

$stats['clientes'] = db()->query("SELECT COUNT(*) FROM clientes WHERE activo = 1")->fetchColumn();

$stats['activas'] = db()->query(
    "SELECT COUNT(DISTINCT cliente_id) FROM compras
     WHERE status = 'completado' AND fecha_fin >= CURDATE()"
)->fetchColumn();

$stats['ingresos_mes'] = db()->query(
    "SELECT COALESCE(SUM(monto),0) FROM compras
     WHERE status = 'completado'
       AND MONTH(fecha_pago) = MONTH(NOW())
       AND YEAR(fecha_pago) = YEAR(NOW())"
)->fetchColumn();

$stats['ingresos_total'] = db()->query(
    "SELECT COALESCE(SUM(monto),0) FROM compras WHERE status = 'completado'"
)->fetchColumn();

// Últimas 8 compras
$ultimas = db()->query(
    "SELECT c.*, cl.nombre AS cliente_nombre, cl.email AS cliente_email,
            m.nombre AS membresia_nombre
     FROM compras c
     JOIN clientes cl ON cl.id = c.cliente_id
     JOIN membresias m ON m.id = c.membresia_id
     ORDER BY c.created_at DESC
     LIMIT 8"
)->fetchAll();

// Top membresías
$top = db()->query(
    "SELECT m.nombre, COUNT(c.id) AS total, SUM(c.monto) AS ingresos
     FROM compras c
     JOIN membresias m ON m.id = c.membresia_id
     WHERE c.status = 'completado'
     GROUP BY m.id
     ORDER BY total DESC
     LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Dashboard — Admin EgoGym</title>
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
      <div class="page-header">
        <h1>Dashboard</h1>
        <p>Resumen general de <?= APP_NAME ?></p>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--tq)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.768-.293-1.47-.773-2M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.768.293-1.47.773-2M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>
          </div>
          <div class="stat-body">
            <p class="stat-label">Clientes</p>
            <p class="stat-value"><?= number_format($stats['clientes']) ?></p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon pu">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--pu)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="stat-body">
            <p class="stat-label">Membresías activas</p>
            <p class="stat-value"><?= number_format($stats['activas']) ?></p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon success">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="stat-body">
            <p class="stat-label">Ingresos este mes</p>
            <p class="stat-value"><?= fmt_precio((float)$stats['ingresos_mes']) ?></p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon warn">
            <svg fill="none" viewBox="0 0 24 24" stroke="var(--warn)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          </div>
          <div class="stat-body">
            <p class="stat-label">Ingresos totales</p>
            <p class="stat-value"><?= fmt_precio((float)$stats['ingresos_total']) ?></p>
          </div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px">
        <!-- Últimas compras -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Últimas compras</h3>
            <a href="pagos.php" class="btn btn-ghost btn-sm">Ver todas</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Cliente</th><th>Membresía</th><th>Monto</th><th>Fecha</th><th>Estado</th></tr>
              </thead>
              <tbody>
                <?php if ($ultimas): foreach ($ultimas as $c): ?>
                  <tr>
                    <td>
                      <div style="font-weight:600;color:var(--white)"><?= h($c['cliente_nombre']) ?></div>
                      <div style="font-size:.78rem;color:var(--muted)"><?= h($c['cliente_email']) ?></div>
                    </td>
                    <td><?= h($c['membresia_nombre']) ?></td>
                    <td style="color:var(--tq);font-weight:600"><?= fmt_precio((float)$c['monto']) ?></td>
                    <td><?= $c['fecha_pago'] ? fmt_fecha($c['fecha_pago']) : '—' ?></td>
                    <td>
                      <?php $badges = ['completado'=>'badge-success','pendiente'=>'badge-warn','fallido'=>'badge-danger','reembolsado'=>'badge-muted']; ?>
                      <span class="badge <?= $badges[$c['status']] ?? 'badge-muted' ?>"><?= h(ucfirst($c['status'])) ?></span>
                    </td>
                  </tr>
                <?php endforeach; else: ?>
                  <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:24px">Sin compras aún</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Top membresías -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Top membresías</h3>
          </div>
          <div class="card-body" style="padding:16px">
            <?php if ($top): foreach ($top as $t): ?>
              <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)">
                <div>
                  <p style="font-weight:600;font-size:.88rem;color:var(--white)"><?= h($t['nombre']) ?></p>
                  <p style="font-size:.76rem;color:var(--muted)"><?= $t['total'] ?> ventas</p>
                </div>
                <span style="color:var(--tq);font-weight:700;font-size:.88rem"><?= fmt_precio((float)$t['ingresos']) ?></span>
              </div>
            <?php endforeach; else: ?>
              <p style="color:var(--muted);text-align:center;padding:24px 0;font-size:.88rem">Sin datos</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); document.getElementById('overlay').classList.toggle('open'); }
function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('overlay').classList.remove('open'); }
</script>
</body>
</html>
