<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$cliente_id = isset($_GET['cliente_id']) && is_numeric($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : 0;
if (!$cliente_id) redirect(APP_URL . '/admin/clientes.php');

$stmt = db()->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $cliente_id]);
$cliente = $stmt->fetch();
if (!$cliente) { flash('error', 'Cliente no encontrado.', 'error'); redirect(APP_URL . '/admin/clientes.php'); }

// Huellas actuales del cliente
$huellas = db()->prepare("SELECT * FROM huellas WHERE cliente_id = :id ORDER BY created_at DESC");
$huellas->execute([':id' => $cliente_id]);
$lista_huellas = $huellas->fetchAll();

$errors = [];

// ── Registrar nueva huella ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    if (!csrf_verify()) {
        $errors[] = 'Token inválido.';
    } else {
        $hash = clean($_POST['huella_hash'] ?? '');

        if (strlen($hash) < 8) {
            $errors[] = 'El hash de huella no es válido.';
        } else {
            // Verificar que no exista ya ese hash
            $dup = db()->prepare("SELECT id FROM huellas WHERE huella_hash = :h LIMIT 1");
            $dup->execute([':h' => $hash]);
            if ($dup->fetch()) {
                $errors[] = 'Esta huella ya está registrada en el sistema.';
            } else {
                db()->prepare(
                    "INSERT INTO huellas (cliente_id, huella_hash, activa) VALUES (:cid, :hash, 1)"
                )->execute([':cid' => $cliente_id, ':hash' => $hash]);

                flash('success', 'Huella registrada correctamente.', 'success');
                redirect(APP_URL . '/admin/registrar_huella.php?cliente_id=' . $cliente_id);
            }
        }
    }
}

// ── Desactivar huella ──────────────────────────────
if (isset($_GET['desactivar']) && is_numeric($_GET['desactivar']) && isset($_GET['csrf']) && hash_equals(csrf_token(), $_GET['csrf'])) {
    db()->prepare("UPDATE huellas SET activa = 0 WHERE id = :id AND cliente_id = :cid")
        ->execute([':id' => (int)$_GET['desactivar'], ':cid' => $cliente_id]);
    flash('success', 'Huella desactivada.', 'success');
    redirect(APP_URL . '/admin/registrar_huella.php?cliente_id=' . $cliente_id);
}

$flash_ok  = get_flash('success');
$flash_err = get_flash('error');
$tiene_huella_activa = !empty(array_filter($lista_huellas, fn($h) => $h['activa']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Registrar huella — Admin EgoGym</title>
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

      <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
        <div>
          <h1>Registro de huella dactilar</h1>
          <p>Cliente: <strong style="color:var(--white)"><?= h($cliente['nombre']) ?></strong> · <?= h($cliente['email']) ?></p>
        </div>
        <a href="cliente_detalle.php?id=<?= $cliente_id ?>" class="btn btn-ghost">← Volver al cliente</a>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

        <!-- Estado actual -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Estado</h3>
          </div>
          <div class="card-body" style="display:flex;flex-direction:column;gap:16px">

            <!-- Indicador -->
            <div style="display:flex;align-items:center;gap:14px;padding:16px;border-radius:10px;background:<?= $tiene_huella_activa ? 'rgba(56,224,160,.07)' : 'rgba(255,180,67,.07)' ?>;border:1px solid <?= $tiene_huella_activa ? 'rgba(56,224,160,.25)' : 'rgba(255,180,67,.25)' ?>">
              <?php if ($tiene_huella_activa): ?>
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <div>
                  <p style="font-weight:700;color:var(--success)">Huella registrada</p>
                  <p style="font-size:.8rem;color:var(--muted)">El cliente puede acceder con huella dactilar.</p>
                </div>
              <?php else: ?>
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="var(--warn)" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                  <p style="font-weight:700;color:var(--warn)">Sin huella registrada</p>
                  <p style="font-size:.8rem;color:var(--muted)">Registra la huella del cliente a continuación.</p>
                </div>
              <?php endif; ?>
            </div>

            <!-- Lista de huellas -->
            <?php if ($lista_huellas): ?>
            <div>
              <p style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--muted);margin-bottom:8px">Huellas registradas</p>
              <?php foreach ($lista_huellas as $h): ?>
              <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--bg3);margin-bottom:6px">
                <div>
                  <code style="font-size:.72rem;color:<?= $h['activa'] ? 'var(--tq)' : 'var(--muted)' ?>"><?= h(substr($h['huella_hash'], 0, 24)) ?>…</code>
                  <div style="font-size:.7rem;color:var(--muted);margin-top:2px"><?= fmt_fecha_hora($h['created_at']) ?></div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                  <span class="badge <?= $h['activa'] ? 'badge-success' : 'badge-muted' ?>">
                    <?= $h['activa'] ? 'Activa' : 'Inactiva' ?>
                  </span>
                  <?php if ($h['activa']): ?>
                  <a href="?cliente_id=<?= $cliente_id ?>&desactivar=<?= $h['id'] ?>&csrf=<?= h(csrf_token()) ?>"
                     class="btn btn-danger btn-sm"
                     onclick="return confirm('¿Desactivar esta huella?')">✕</a>
                  <?php endif; ?>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color:var(--muted);font-size:.85rem">No hay huellas registradas para este cliente.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Formulario de registro -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Registrar nueva huella</h3>
          </div>
          <div class="card-body">

            <?php if ($errors): ?>
              <div class="alert alert-error" style="margin-bottom:16px">
                <?php foreach ($errors as $e): echo h($e) . '<br>'; endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Aviso del lector -->
            <div style="background:rgba(56,224,208,.06);border:1px solid var(--tq-border);border-radius:10px;padding:14px 16px;margin-bottom:20px">
              <p style="font-size:.8rem;color:var(--tq);font-weight:600;margin-bottom:4px">🔌 Lector de huella</p>
              <p style="font-size:.78rem;color:var(--muted);line-height:1.6">
                Cuando el lector esté conectado, al presionar <strong style="color:var(--white)">"Capturar huella"</strong> el dispositivo leerá la huella del cliente y llenará el campo automáticamente. Por ahora puedes ingresar el hash manualmente o usar el botón de prueba.
              </p>
            </div>

            <form method="POST" action="" novalidate id="form-huella">
              <?php csrf_field(); ?>

              <div class="form-group">
                <label for="huella_hash">Hash de huella dactilar</label>
                <div style="display:flex;gap:8px">
                  <input type="text" id="huella_hash" name="huella_hash" class="form-control"
                         placeholder="El lector lo llenará automáticamente…"
                         style="font-family:monospace;font-size:.82rem" required/>
                  <button type="button" class="btn btn-ghost" onclick="capturarHuella()" title="Capturar desde lector" id="btn-capturar" style="flex-shrink:0;white-space:nowrap">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                    Capturar
                  </button>
                </div>
                <p class="form-hint">El lector enviará el hash automáticamente al presionar "Capturar".</p>
              </div>

              <!-- Botón de prueba (solo en desarrollo) -->
              <div style="margin-bottom:16px">
                <button type="button" class="btn btn-ghost btn-sm" onclick="generarHashPrueba()" style="font-size:.76rem;color:var(--muted)">
                  Generar hash de prueba
                </button>
                <span style="font-size:.72rem;color:var(--muted);margin-left:8px">(solo para desarrollo)</span>
              </div>

              <div style="display:flex;gap:10px">
                <button type="submit" name="registrar" class="btn btn-primary">
                  Registrar huella
                </button>
                <a href="cliente_detalle.php?id=<?= $cliente_id ?>" class="btn btn-ghost">
                  <?= $tiene_huella_activa ? 'Cancelar' : 'Omitir por ahora' ?>
                </a>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('open');}

// Simula la captura desde el lector (cuando esté conectado, aquí irá la llamada real al dispositivo)
function capturarHuella() {
  const btn = document.getElementById('btn-capturar');
  const input = document.getElementById('huella_hash');
  btn.textContent = 'Leyendo…';
  btn.disabled = true;
  // TODO: reemplazar este setTimeout con la llamada real al SDK del lector
  setTimeout(() => {
    // Simulado — el lector real devolvería el hash aquí
    input.value = '';
    input.placeholder = 'Acerca el dedo al lector…';
    btn.textContent = '⏳ Esperando';
    setTimeout(() => {
      btn.innerHTML = '<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg> Capturar';
      btn.disabled = false;
      input.placeholder = 'Conecta el lector para capturar';
    }, 2000);
  }, 500);
}

// Genera un hash aleatorio para pruebas
function generarHashPrueba() {
  const chars = '0123456789abcdef';
  let hash = 'fp_test_';
  for (let i = 0; i < 32; i++) hash += chars[Math.floor(Math.random() * chars.length)];
  document.getElementById('huella_hash').value = hash;
}
</script>
</body>
</html>
