<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/stripe.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    flash('error', 'Solicitud inválida.', 'error');
    redirect(APP_URL . '/cliente/membresias.php');
}

$membresia_id = (int)($_POST['membresia_id'] ?? 0);

// Verificar membresía
$stmt = db()->prepare("SELECT * FROM membresias WHERE id = :id AND activa = 1 LIMIT 1");
$stmt->execute([':id' => $membresia_id]);
$membresia = $stmt->fetch();

if (!$membresia) {
    flash('error', 'Membresía no disponible.', 'error');
    redirect(APP_URL . '/cliente/membresias.php');
}

$cliente_id    = (int)$_SESSION['cliente_id'];
$cliente_email = $_SESSION['cliente_email'];

try {
    Stripe::setApiKey(STRIPE_SECRET_KEY);

    $session = Session::create([
        'payment_method_types' => ['card'],
        'mode'                 => 'payment',
        'customer_email'       => $cliente_email,
        'line_items'           => [[
            'price_data' => [
                'currency'     => STRIPE_CURRENCY,
                'unit_amount'  => (int)($membresia['precio'] * 100),
                'product_data' => [
                    'name'        => 'EgoGym — ' . $membresia['nombre'],
                    'description' => $membresia['descripcion'] ?? '',
                ],
            ],
            'quantity' => 1,
        ]],
        'metadata' => [
            'cliente_id'   => $cliente_id,
            'membresia_id' => $membresia['id'],
        ],
        'success_url' => APP_URL . '/cliente/exito.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'  => APP_URL . '/cliente/cancelado.php',
    ]);

    // Guardar compra pendiente
    $hoy   = date('Y-m-d');
    $fin   = date('Y-m-d', strtotime("+{$membresia['duracion_dias']} days"));
    $ins   = db()->prepare(
        "INSERT INTO compras (cliente_id, membresia_id, stripe_session_id, monto, fecha_inicio, fecha_fin, status)
         VALUES (:cid, :mid, :sid, :monto, :fi, :ff, 'pendiente')"
    );
    $ins->execute([
        ':cid'   => $cliente_id,
        ':mid'   => $membresia['id'],
        ':sid'   => $session->id,
        ':monto' => $membresia['precio'],
        ':fi'    => $hoy,
        ':ff'    => $fin,
    ]);

    header('Location: ' . $session->url);
    exit;

} catch (\Stripe\Exception\ApiErrorException $e) {
    flash('error', 'Error al procesar el pago: ' . $e->getMessage(), 'error');
    redirect(APP_URL . '/cliente/membresias.php');
}
