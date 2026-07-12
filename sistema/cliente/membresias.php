<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$cliente_nombre = $_SESSION['cliente_nombre'];

// Traer membresías activas
$stmt = db()->query("SELECT * FROM membresias WHERE activa = 1 ORDER BY precio ASC");
$membresias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Membresías — EgoGym Fitness</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
</head>
<body>
<div class="shell">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <a href="../../index.php"><em>EGOGYM</em> FITNESS</a>
      <span>Panel cliente</span>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php">
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Mi cuenta
      </a>
      <a href="membresias.php" class="active">
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

  <div class="main">
    <header class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <span class="topbar-title">Membresías</span>
      </div>
      <div class="topbar-user">
        <span><?= h($cliente_nombre) ?></span>
        <div class="topbar-avatar"><?= strtoupper(substr($cliente_nombre, 0, 1)) ?></div>
      </div>
    </header>

    <div class="content">
      <div class="page-header">
        <h1>Elige tu membresía</h1>
        <p>Sin costo de inscripción. Acceso inmediato tras el pago.</p>
      </div>

      <?php render_flash('error'); ?>

      <div class="membresias-grid">
        <?php foreach ($membresias as $m): ?>
          <?php $beneficios = json_decode($m['beneficios'] ?? '[]', true) ?: []; ?>
          <div class="membresia-card <?= $m['destacada'] ? 'destacada' : '' ?>">
            <?php if ($m['destacada']): ?>
              <div class="badge-destacada">Más popular</div>
            <?php endif; ?>

            <div>
              <p class="membresia-duracion"><?= h(dias_texto((int)$m['duracion_dias'])) ?></p>
              <h2 class="membresia-nombre"><?= h($m['nombre']) ?></h2>
            </div>

            <div class="membresia-precio">
              <strong><?= fmt_precio((float)$m['precio']) ?></strong>
            </div>

            <?php if ($m['descripcion']): ?>
              <p style="font-size:.85rem;color:var(--muted)"><?= h($m['descripcion']) ?></p>
            <?php endif; ?>

            <?php if ($beneficios): ?>
              <ul class="membresia-beneficios">
                <?php foreach ($beneficios as $b): ?>
                  <li><?= h($b) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <form method="POST" action="checkout.php">
              <?php csrf_field(); ?>
              <input type="hidden" name="membresia_id" value="<?= (int)$m['id'] ?>"/>
              <button type="submit" class="btn <?= $m['destacada'] ? 'btn-primary' : 'btn-outline' ?> btn-full">
                Comprar
              </button>
            </form>
          </div>
        <?php endforeach; ?>
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
