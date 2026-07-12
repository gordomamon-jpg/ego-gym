<header class="topbar">
  <div style="display:flex;align-items:center;gap:12px">
    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Menú">
      <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
    <span class="topbar-title"><?= APP_NAME ?></span>
  </div>
  <div class="topbar-user">
    <span><?= h($_SESSION['admin_nombre'] ?? '') ?></span>
    <div class="topbar-avatar" style="background:linear-gradient(135deg,var(--pu),var(--tq))">
      <?= strtoupper(substr($_SESSION['admin_nombre'] ?? 'A', 0, 1)) ?>
    </div>
  </div>
</header>
