<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-logo">
      <a href="<?= APP_URL ?>/../../index.php"><em>EGOGYM</em> FITNESS</a>
    <span>Panel Admin</span>
  </div>
  <nav class="sidebar-nav">
    <span class="nav-section">General</span>
    <a href="dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">
      <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>

    <span class="nav-section">Gestión</span>
    <a href="clientes.php" class="<?= in_array($current, ['clientes.php','cliente_form.php','cliente_detalle.php','asignar_plan.php']) ? 'active' : '' ?>">
      <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.768-.293-1.47-.773-2M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.768.293-1.47.773-2M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>
      Clientes
    </a>
    <a href="membresias.php" class="<?= in_array($current, ['membresias.php','membresia_form.php']) ? 'active' : '' ?>">
      <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
      Membresías
    </a>
    <a href="pagos.php" class="<?= $current === 'pagos.php' ? 'active' : '' ?>">
      <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 16h1m4 0h1M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      Pagos
    </a>

    <span class="nav-section">Control de Acceso</span>
    <a href="accesos.php" class="<?= $current === 'accesos.php' ? 'active' : '' ?>">
      <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      Accesos
    </a>
    <a href="kiosko.php" class="<?= $current === 'kiosko.php' ? 'active' : '' ?>" target="_blank" title="Abre en nueva pestaña — modo pantalla completa">
      <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
      Kiosko de Acceso
    </a>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php">
      <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      Cerrar sesión
    </a>
  </div>
</aside>
