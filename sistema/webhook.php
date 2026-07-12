<?php
// ══════════════════════════════════════════════════
//  EgoGym — Stripe Webhook Handler
//  Registrado en: Dashboard Stripe → Webhooks
//  Evento: checkout.session.completed
// ══════════════════════════════════════════════════

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/stripe.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Webhook;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// Stripe requiere el body crudo
$payload   = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = Webhook::constructEvent($payload, $sig_header, STRIPE_WEBHOOK_SECRET);
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    exit('Invalid payload');
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit('Invalid signature');
}

if ($event->type === 'checkout.session.completed') {
    $session    = $event->data->object;
    $session_id = $session->id;
    $payment_id = $session->payment_intent;

    // Buscar compra pendiente
    $stmt = db()->prepare(
        "SELECT c.*, cl.nombre AS cliente_nombre, cl.email AS cliente_email,
                m.nombre AS membresia_nombre
         FROM compras c
         JOIN clientes cl ON cl.id = c.cliente_id
         JOIN membresias m ON m.id = c.membresia_id
         WHERE c.stripe_session_id = :sid AND c.status = 'pendiente'
         LIMIT 1"
    );
    $stmt->execute([':sid' => $session_id]);
    $compra = $stmt->fetch();

    if ($compra) {
        // Marcar como completada
        $upd = db()->prepare(
            "UPDATE compras
             SET status = 'completado',
                 stripe_payment_id = :pid,
                 fecha_pago = NOW()
             WHERE id = :id"
        );
        $upd->execute([
            ':pid' => $payment_id,
            ':id'  => $compra['id'],
        ]);

        // Enviar correo al cliente y al admin
        enviar_correo_confirmacion($compra);
        enviar_correo_admin($compra);
    }
}

http_response_code(200);
echo 'OK';

// ── Funciones de correo ────────────────────────────

function enviar_correo_confirmacion(array $compra): void {
    $mail = new_mailer();
    try {
        $mail->addAddress($compra['cliente_email'], $compra['cliente_nombre']);
        $mail->Subject = '¡Membresía activada! — ' . APP_NAME;
        $mail->isHTML(true);
        $mail->Body = correo_cliente_html($compra);
        $mail->AltBody = correo_cliente_texto($compra);
        $mail->send();

        db()->prepare("UPDATE compras SET correo_enviado = 1 WHERE id = :id")
            ->execute([':id' => $compra['id']]);
    } catch (PHPMailerException $e) {
        error_log('Mail cliente error: ' . $e->getMessage());
    }
}

function enviar_correo_admin(array $compra): void {
    $mail = new_mailer();
    try {
        $mail->addAddress(MAIL_ADMIN, APP_NAME . ' Admin');
        $mail->Subject = 'Nueva compra — ' . $compra['membresia_nombre'] . ' — ' . APP_NAME;
        $mail->isHTML(true);
        $mail->Body = correo_admin_html($compra);
        $mail->AltBody = correo_admin_texto($compra);
        $mail->send();
    } catch (PHPMailerException $e) {
        error_log('Mail admin error: ' . $e->getMessage());
    }
}

function new_mailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = MAIL_PORT;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    return $mail;
}

function correo_cliente_html(array $c): string {
    $nombre   = htmlspecialchars($c['cliente_nombre']);
    $memb     = htmlspecialchars($c['membresia_nombre']);
    $monto    = '$' . number_format($c['monto'], 2) . ' MXN';
    $inicio   = date('d/m/Y', strtotime($c['fecha_inicio']));
    $fin      = date('d/m/Y', strtotime($c['fecha_fin']));
    $appUrl   = APP_URL;
    $appName  = APP_NAME;
    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/></head>
<body style="margin:0;padding:0;background:#0B0B0F;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;color:#D4D4F0">
  <div style="max-width:520px;margin:40px auto;padding:0 16px">
    <div style="background:#141419;border:1px solid rgba(56,224,208,.22);border-radius:18px;overflow:hidden">
      <!-- Header -->
      <div style="background:linear-gradient(135deg,rgba(122,60,255,.3),rgba(56,224,208,.2));padding:28px 28px 20px;text-align:center">
        <p style="font-size:.7rem;font-weight:700;letter-spacing:4px;text-transform:uppercase;color:#38E0D0;margin-bottom:6px">EgoGym Fitness</p>
        <h1 style="font-size:1.6rem;font-weight:900;text-transform:uppercase;letter-spacing:1px;color:#fff;margin:0">¡Membresía Activa!</h1>
      </div>
      <!-- Body -->
      <div style="padding:28px">
        <p style="margin-bottom:16px">Hola <strong>{$nombre}</strong>,</p>
        <p style="margin-bottom:24px;color:#6A6A90">Tu compra se procesó exitosamente. Ya puedes venir a entrenar.</p>
        <!-- Detalles -->
        <div style="background:#18181F;border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:18px;margin-bottom:24px">
          <table style="width:100%;border-collapse:collapse;font-size:.88rem">
            <tr><td style="padding:7px 0;color:#6A6A90">Membresía</td><td style="text-align:right;font-weight:600;color:#fff">{$memb}</td></tr>
            <tr><td style="padding:7px 0;color:#6A6A90;border-top:1px solid rgba(255,255,255,.07)">Monto</td><td style="text-align:right;font-weight:600;color:#38E0D0;border-top:1px solid rgba(255,255,255,.07)">{$monto}</td></tr>
            <tr><td style="padding:7px 0;color:#6A6A90;border-top:1px solid rgba(255,255,255,.07)">Inicio</td><td style="text-align:right;border-top:1px solid rgba(255,255,255,.07)">{$inicio}</td></tr>
            <tr><td style="padding:7px 0;color:#6A6A90;border-top:1px solid rgba(255,255,255,.07)">Vencimiento</td><td style="text-align:right;border-top:1px solid rgba(255,255,255,.07)">{$fin}</td></tr>
          </table>
        </div>
        <div style="text-align:center">
          <a href="{$appUrl}/cliente/dashboard.php" style="display:inline-block;background:linear-gradient(90deg,#38E0D0,#7A3CFF);color:#0B0B0F;font-weight:800;font-size:.85rem;letter-spacing:2px;text-transform:uppercase;padding:13px 28px;border-radius:10px;text-decoration:none">Ver mi cuenta</a>
        </div>
      </div>
      <div style="padding:16px 28px;border-top:1px solid rgba(255,255,255,.07);text-align:center;font-size:.76rem;color:#6A6A90">
        {$appName} · Miguel Hidalgo, CDMX · Todos los días incluyendo festivos
      </div>
    </div>
  </div>
</body>
</html>
HTML;
}

function correo_cliente_texto(array $c): string {
    return sprintf(
        "EgoGym Fitness — Membresía activada\n\nHola %s,\nTu membresía %s está activa.\nMonto: $%.2f MXN\nInicio: %s\nVencimiento: %s\n\nVer cuenta: %s/cliente/dashboard.php",
        $c['cliente_nombre'], $c['membresia_nombre'], $c['monto'],
        $c['fecha_inicio'], $c['fecha_fin'], APP_URL
    );
}

function correo_admin_html(array $c): string {
    $nombre  = htmlspecialchars($c['cliente_nombre']);
    $email   = htmlspecialchars($c['cliente_email']);
    $memb    = htmlspecialchars($c['membresia_nombre']);
    $monto   = '$' . number_format($c['monto'], 2) . ' MXN';
    $inicio  = date('d/m/Y', strtotime($c['fecha_inicio']));
    $fin     = date('d/m/Y', strtotime($c['fecha_fin']));
    $appUrl  = APP_URL;
    return <<<HTML
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"/></head>
<body style="margin:0;padding:0;background:#0B0B0F;font-family:Helvetica,Arial,sans-serif;color:#D4D4F0">
  <div style="max-width:480px;margin:32px auto;padding:0 16px">
    <div style="background:#141419;border:1px solid rgba(56,224,208,.22);border-radius:16px;overflow:hidden">
      <div style="background:linear-gradient(90deg,rgba(122,60,255,.25),rgba(56,224,208,.15));padding:20px 24px">
        <p style="font-size:.7rem;letter-spacing:3px;text-transform:uppercase;color:#38E0D0;margin:0 0 4px">Admin</p>
        <h2 style="margin:0;font-size:1.2rem;color:#fff">Nueva compra registrada</h2>
      </div>
      <div style="padding:22px 24px">
        <table style="width:100%;border-collapse:collapse;font-size:.87rem">
          <tr><td style="padding:6px 0;color:#6A6A90">Cliente</td><td style="text-align:right;font-weight:600;color:#fff">{$nombre}</td></tr>
          <tr><td style="padding:6px 0;color:#6A6A90;border-top:1px solid rgba(255,255,255,.07)">Email</td><td style="text-align:right;border-top:1px solid rgba(255,255,255,.07)">{$email}</td></tr>
          <tr><td style="padding:6px 0;color:#6A6A90;border-top:1px solid rgba(255,255,255,.07)">Membresía</td><td style="text-align:right;font-weight:600;color:#fff;border-top:1px solid rgba(255,255,255,.07)">{$memb}</td></tr>
          <tr><td style="padding:6px 0;color:#6A6A90;border-top:1px solid rgba(255,255,255,.07)">Monto</td><td style="text-align:right;font-weight:700;color:#38E0D0;border-top:1px solid rgba(255,255,255,.07)">{$monto}</td></tr>
          <tr><td style="padding:6px 0;color:#6A6A90;border-top:1px solid rgba(255,255,255,.07)">Inicio</td><td style="text-align:right;border-top:1px solid rgba(255,255,255,.07)">{$inicio}</td></tr>
          <tr><td style="padding:6px 0;color:#6A6A90;border-top:1px solid rgba(255,255,255,.07)">Vencimiento</td><td style="text-align:right;border-top:1px solid rgba(255,255,255,.07)">{$fin}</td></tr>
        </table>
        <div style="margin-top:18px;text-align:center">
          <a href="{$appUrl}/admin/pagos.php" style="display:inline-block;background:linear-gradient(90deg,#38E0D0,#7A3CFF);color:#0B0B0F;font-weight:700;font-size:.8rem;letter-spacing:2px;text-transform:uppercase;padding:11px 24px;border-radius:8px;text-decoration:none">Ver en panel admin</a>
        </div>
      </div>
    </div>
  </div>
</body></html>
HTML;
}

function correo_admin_texto(array $c): string {
    return sprintf(
        "Nueva compra — EgoGym Fitness\n\nCliente: %s (%s)\nMembresía: %s\nMonto: $%.2f MXN\nInicio: %s | Vencimiento: %s\n\nPanel: %s/admin/pagos.php",
        $c['cliente_nombre'], $c['cliente_email'], $c['membresia_nombre'],
        $c['monto'], $c['fecha_inicio'], $c['fecha_fin'], APP_URL
    );
}
