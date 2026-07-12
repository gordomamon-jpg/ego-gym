<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/stripe.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

$session_id = clean($_GET['session_id'] ?? '');

if (!$session_id) {
    redirect(APP_URL . '/cliente/dashboard.php');
}

$compra = null;
try {
    Stripe::setApiKey(STRIPE_SECRET_KEY);
    $stripe_session = Session::retrieve($session_id);

    if ($stripe_session->payment_status === 'paid') {
        // Buscar compra pendiente por session_id
        $stmt = db()->prepare(
            "SELECT c.*, m.nombre AS membresia_nombre
             FROM compras c
             JOIN membresias m ON m.id = c.membresia_id
             WHERE c.stripe_session_id = :sid AND c.cliente_id = :cid
             LIMIT 1"
        );
        $stmt->execute([
            ':sid' => $session_id,
            ':cid' => (int)$_SESSION['cliente_id'],
        ]);
        $compra = $stmt->fetch();
    }
} catch (\Exception $e) {
    // El webhook ya habrá procesado el pago; solo mostramos confirmación
}

$nombre = $_SESSION['cliente_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>¡Pago exitoso! — EgoGym Fitness</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
</head>
<body>
<div class="auth-page">
  <div class="auth-box" style="max-width:480px">
    <div class="auth-logo">
      <a href="../../index.php"><em>EGOGYM</em> FITNESS</a>
    </div>

    <div class="auth-card" style="text-align:center">
      <!-- Check animado -->
      <div style="display:flex;justify-content:center;margin-bottom:20px">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--success-lo);border:1px solid rgba(56,224,160,.3);display:flex;align-items:center;justify-content:center">
          <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="var(--success)" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
      </div>

      <h2 style="margin-bottom:8px">¡Pago completado!</h2>
      <p style="color:var(--muted);margin-bottom:24px">
        Hola <?= h(explode(' ', $nombre)[0]) ?>, tu membresía está activa.
        Recibirás un correo de confirmación en breve.
      </p>

      <?php if ($compra): ?>
        <div style="background:var(--bg3);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px;text-align:left;margin-bottom:24px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:.85rem">
            <div>
              <p style="color:var(--muted);font-size:.72rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Membresía</p>
              <p style="font-weight:600"><?= h($compra['membresia_nombre']) ?></p>
            </div>
            <div>
              <p style="color:var(--muted);font-size:.72rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Monto</p>
              <p style="font-weight:600"><?= fmt_precio((float)$compra['monto']) ?></p>
            </div>
            <div>
              <p style="color:var(--muted);font-size:.72rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Inicio</p>
              <p><?= fmt_fecha($compra['fecha_inicio']) ?></p>
            </div>
            <div>
              <p style="color:var(--muted);font-size:.72rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:2px">Vencimiento</p>
              <p><?= fmt_fecha($compra['fecha_fin']) ?></p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <a href="dashboard.php" class="btn btn-primary btn-full btn-lg">
        Ir a mi cuenta
      </a>

      <!-- Aviso de huella -->
      <div style="margin-top:20px;background:rgba(255,180,67,.07);border:1px solid rgba(255,180,67,.25);border-radius:12px;padding:18px 20px;text-align:left">
        <div style="display:flex;gap:12px;align-items:flex-start">
          <div style="flex-shrink:0;margin-top:2px">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--warn)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          </div>
          <div>
            <p style="font-family:var(--fh);font-weight:700;font-size:.95rem;text-transform:uppercase;letter-spacing:.5px;color:var(--warn);margin-bottom:4px">Próximo paso — Registro de huella</p>
            <p style="font-size:.84rem;color:var(--light);line-height:1.6">
              Para acceder a las instalaciones necesitas registrar tu huella dactilar. <strong style="color:var(--white)">Acude al gym</strong> y pide al staff que registre tu huella en el sistema. Solo se hace una vez.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
