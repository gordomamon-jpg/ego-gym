<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$cliente_id     = (int)$_SESSION['cliente_id'];
$cliente_nombre = $_SESSION['cliente_nombre'];
$cliente_email  = $_SESSION['cliente_email'];

// Cargar datos actuales
$stmt = db()->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $cliente_id]);
$cliente = $stmt->fetch();

$errors   = [];
$success  = false;
$tab      = clean($_GET['tab'] ?? 'datos'); // 'datos' | 'password'

// ── Actualizar datos personales ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_datos'])) {
    if (!csrf_verify()) {
        $errors[] = 'Token inválido. Recarga la página.';
    } else {
        $nombre   = clean($_POST['nombre']   ?? '');
        $telefono = clean($_POST['telefono'] ?? '');

        if (strlen($nombre) < 2) $errors[] = 'El nombre debe tener al menos 2 caracteres.';

        if (empty($errors)) {
            db()->prepare("UPDATE clientes SET nombre=:n, telefono=:t WHERE id=:id")
                ->execute([':n' => $nombre, ':t' => $telefono ?: null, ':id' => $cliente_id]);

            $_SESSION['cliente_nombre'] = $nombre;
            $cliente_nombre = $nombre;
            $cliente['nombre']   = $nombre;
            $cliente['telefono'] = $telefono;

            flash('success', 'Datos actualizados correctamente.', 'success');
            redirect(APP_URL . '/cliente/perfil.php?tab=datos');
        }
        $tab = 'datos';
    }
}

// ── Cambiar contraseña ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    if (!csrf_verify()) {
        $errors[] = 'Token inválido. Recarga la página.';
    } else {
        $actual    = $_POST['password_actual']  ?? '';
        $nueva     = $_POST['password_nueva']   ?? '';
        $confirmar = $_POST['password_confirmar']?? '';

        if (!password_verify($actual, $cliente['password_hash'])) {
            $errors[] = 'La contraseña actual no es correcta.';
        }
        if (strlen($nueva) < 8) {
            $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        }
        if ($nueva !== $confirmar) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (empty($errors)) {
            $hash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]);
            db()->prepare("UPDATE clientes SET password_hash=:h WHERE id=:id")
                ->execute([':h' => $hash, ':id' => $cliente_id]);

            flash('success', 'Contraseña actualizada correctamente.', 'success');
            redirect(APP_URL . '/cliente/perfil.php?tab=password');
        }
        $tab = 'password';
    }
}

$flash_ok  = get_flash('success');
$flash_err = get_flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Mi perfil — EgoGym Fitness</title>
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
      <a href="membresias.php">
        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M3 10h18M7 3v18m10-18v18M3 7a4 4 0 014-4h10a4 4 0 014 4v10a4 4 0 01-4 4H7a4 4 0 01-4-4V7z"/></svg>
        Membresías
      </a>
      <a href="perfil.php" class="active">
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
        <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Menú">
          <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <span class="topbar-title">Mi perfil</span>
      </div>
      <div class="topbar-user">
        <span><?= h($cliente_nombre) ?></span>
        <div class="topbar-avatar"><?= strtoupper(substr($cliente_nombre, 0, 1)) ?></div>
      </div>
    </header>

    <div class="content">
      <div class="page-header">
        <h1>Mi perfil</h1>
        <p>Gestiona tu información personal y contraseña</p>
      </div>

      <?php if ($flash_ok): ?>
        <div class="alert alert-success" style="margin-bottom:20px"><?= h($flash_ok['message']) ?></div>
      <?php endif; ?>
      <?php if ($flash_err): ?>
        <div class="alert alert-error" style="margin-bottom:20px"><?= h($flash_err['message']) ?></div>
      <?php endif; ?>

      <!-- Tabs -->
      <div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:0">
        <a href="perfil.php?tab=datos"
           style="padding:10px 20px;font-family:var(--fh);font-weight:700;font-size:.85rem;letter-spacing:1px;text-transform:uppercase;border-bottom:2px solid <?= $tab === 'datos' ? 'var(--tq)' : 'transparent' ?>;color:<?= $tab === 'datos' ? 'var(--tq)' : 'var(--muted)' ?>;transition:color .2s">
          Datos personales
        </a>
        <a href="perfil.php?tab=password"
           style="padding:10px 20px;font-family:var(--fh);font-weight:700;font-size:.85rem;letter-spacing:1px;text-transform:uppercase;border-bottom:2px solid <?= $tab === 'password' ? 'var(--tq)' : 'transparent' ?>;color:<?= $tab === 'password' ? 'var(--tq)' : 'var(--muted)' ?>;transition:color .2s">
          Contraseña
        </a>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error" style="margin-bottom:20px">
          <?php foreach ($errors as $e): echo h($e) . '<br>'; endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Tab: Datos personales -->
      <?php if ($tab === 'datos'): ?>
      <div class="card" style="max-width:520px">
        <div class="card-header"><h3 class="card-title">Datos personales</h3></div>
        <div class="card-body">
          <form method="POST" action="perfil.php?tab=datos" novalidate>
            <?php csrf_field(); ?>

            <div class="form-group">
              <label for="nombre">Nombre completo</label>
              <input type="text" id="nombre" name="nombre" class="form-control"
                     value="<?= h($cliente['nombre']) ?>" required/>
            </div>

            <div class="form-group">
              <label for="email">Correo electrónico</label>
              <input type="email" id="email" class="form-control"
                     value="<?= h($cliente['email']) ?>" disabled
                     style="opacity:.5;cursor:not-allowed"/>
              <p class="form-hint">El correo no se puede cambiar. Contacta al gym si necesitas actualizarlo.</p>
            </div>

            <div class="form-group">
              <label for="telefono">Teléfono <span style="color:var(--muted);font-weight:400">(opcional)</span></label>
              <input type="tel" id="telefono" name="telefono" class="form-control"
                     value="<?= h($cliente['telefono'] ?? '') ?>" placeholder="55 1234 5678"/>
            </div>

            <button type="submit" name="guardar_datos" class="btn btn-primary">Guardar cambios</button>
          </form>
        </div>
      </div>

      <!-- Tab: Contraseña -->
      <?php else: ?>
      <div class="card" style="max-width:520px">
        <div class="card-header"><h3 class="card-title">Cambiar contraseña</h3></div>
        <div class="card-body">
          <form method="POST" action="perfil.php?tab=password" novalidate>
            <?php csrf_field(); ?>

            <div class="form-group">
              <label for="password_actual">Contraseña actual</label>
              <input type="password" id="password_actual" name="password_actual" class="form-control"
                     placeholder="Tu contraseña actual" required autocomplete="current-password"/>
            </div>

            <div class="form-group">
              <label for="password_nueva">Nueva contraseña</label>
              <input type="password" id="password_nueva" name="password_nueva" class="form-control"
                     placeholder="Mínimo 8 caracteres" required autocomplete="new-password"/>
            </div>

            <div class="form-group">
              <label for="password_confirmar">Confirmar nueva contraseña</label>
              <input type="password" id="password_confirmar" name="password_confirmar" class="form-control"
                     placeholder="Repite la nueva contraseña" required autocomplete="new-password"/>
            </div>

            <button type="submit" name="cambiar_password" class="btn btn-primary">Actualizar contraseña</button>
          </form>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('open');}
</script>
</body>
</html>
