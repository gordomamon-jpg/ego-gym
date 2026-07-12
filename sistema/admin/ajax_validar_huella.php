<?php
// Endpoint AJAX — valida huella desde el kiosko de acceso admin
// Requiere sesión de admin activa
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$hash = trim($data['huella_hash'] ?? '');

if ($hash === '') {
    echo json_encode(['permitido' => false, 'mensaje' => 'Hash vacío']);
    exit;
}

// Buscar cliente por hash de huella
$stmt = db()->prepare(
    "SELECT c.* FROM clientes c
     JOIN huellas h ON h.cliente_id = c.id
     WHERE h.huella_hash = :hash AND h.activa = 1 AND c.activo = 1
     LIMIT 1"
);
$stmt->execute([':hash' => $hash]);
$cliente = $stmt->fetch();

if (!$cliente) {
    // Log intento no reconocido — no tenemos cliente_id, usamos 0 si la tabla lo permite
    // Solo logueamos si hay un cliente_id válido
    echo json_encode([
        'permitido' => false,
        'mensaje'   => 'Huella no reconocida',
    ]);
    exit;
}

// Verificar membresía activa
$activa = membresia_activa((int)$cliente['id']);

if ($activa) {
    $hoy = new DateTime('today');
    $fin = new DateTime($activa['fecha_fin']);
    $dias_restantes = max(0, (int)$hoy->diff($fin)->days);

    // Registrar acceso
    db()->prepare(
        "INSERT INTO registros_acceso (cliente_id, tipo, metodo, resultado, admin_id)
         VALUES (:cid, 'entrada', 'huella', 'permitido', :aid)"
    )->execute([':cid' => $cliente['id'], ':aid' => $_SESSION['admin_id']]);

    echo json_encode([
        'permitido' => true,
        'cliente'   => ['id' => $cliente['id'], 'nombre' => $cliente['nombre']],
        'membresia' => [
            'nombre'         => $activa['membresia_nombre'],
            'dias_restantes' => $dias_restantes,
            'fecha_fin'      => date('d/m/Y', strtotime($activa['fecha_fin'])),
        ],
        'mensaje' => 'Acceso permitido',
    ]);
} else {
    // Registrar denegado
    db()->prepare(
        "INSERT INTO registros_acceso (cliente_id, tipo, metodo, resultado, motivo, admin_id)
         VALUES (:cid, 'entrada', 'huella', 'denegado', 'Sin membresía activa', :aid)"
    )->execute([':cid' => $cliente['id'], ':aid' => $_SESSION['admin_id']]);

    echo json_encode([
        'permitido' => false,
        'cliente'   => ['id' => $cliente['id'], 'nombre' => $cliente['nombre']],
        'mensaje'   => 'Sin membresía activa',
    ]);
}
