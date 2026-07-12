<?php
// ══════════════════════════════════════════════════
//  EgoGym — API de Control de Acceso
//  Endpoint para lector de huella / dispositivos externos
//
//  Método:  POST (application/json)
//  Body:
//    {
//      "api_key":    "TU_API_KEY",
//      "cliente_id": 123,          // o usa huella_hash
//      "huella_hash":"abc123...",  // si el lector envía el hash
//      "tipo":       "entrada",    // "entrada" | "salida"
//      "metodo":     "huella"      // "huella" | "api"
//    }
//
//  Respuesta 200:
//    {
//      "ok": true,
//      "permitido": true,
//      "cliente": { "id": 1, "nombre": "Juan García" },
//      "membresia": { "nombre": "Mensual", "fecha_fin": "2026-05-01", "dias_restantes": 25 },
//      "mensaje": "Acceso permitido — Mensual vence 01/05/2026"
//    }
//
//  Respuesta denegada:
//    {
//      "ok": true,
//      "permitido": false,
//      "cliente": { "id": 1, "nombre": "Juan García" },
//      "mensaje": "Sin membresía activa"
//    }
// ══════════════════════════════════════════════════

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// ── Solo POST ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

// ── Leer body JSON ────────────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
    exit;
}

// ── Verificar API Key ─────────────────────────────
$api_key_recibida = trim($data['api_key'] ?? '');
if (!defined('EGOGYM_API_KEY') || !hash_equals(EGOGYM_API_KEY, $api_key_recibida)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'API key inválida']);
    exit;
}

// ── Parámetros ────────────────────────────────────
$tipo        = in_array($data['tipo'] ?? '', ['entrada','salida']) ? $data['tipo'] : 'entrada';
$metodo      = in_array($data['metodo'] ?? '', ['huella','api']) ? $data['metodo'] : 'api';
$huella_hash = trim($data['huella_hash'] ?? '');
$cliente_id  = isset($data['cliente_id']) ? (int)$data['cliente_id'] : 0;

// ── Resolver cliente ──────────────────────────────
$cliente = null;

if ($huella_hash !== '') {
    // Buscar por hash de huella
    $stmt = db()->prepare(
        "SELECT c.* FROM clientes c
         JOIN huellas h ON h.cliente_id = c.id
         WHERE h.huella_hash = :hash AND h.activa = 1 AND c.activo = 1
         LIMIT 1"
    );
    $stmt->execute([':hash' => $huella_hash]);
    $cliente = $stmt->fetch();
} elseif ($cliente_id > 0) {
    $stmt = db()->prepare("SELECT * FROM clientes WHERE id = :id AND activo = 1 LIMIT 1");
    $stmt->execute([':id' => $cliente_id]);
    $cliente = $stmt->fetch();
}

if (!$cliente) {
    // Registrar intento fallido si hay cliente_id
    if ($cliente_id > 0) {
        db()->prepare(
            "INSERT INTO registros_acceso (cliente_id, tipo, metodo, resultado, motivo)
             VALUES (:cid, :tipo, :metodo, 'denegado', 'Cliente no encontrado o inactivo')"
        )->execute([':cid' => $cliente_id ?: 0, ':tipo' => $tipo, ':metodo' => $metodo]);
    }
    http_response_code(404);
    echo json_encode(['ok' => true, 'permitido' => false, 'mensaje' => 'Cliente no encontrado']);
    exit;
}

// ── Verificar membresía activa ────────────────────
$activa = membresia_activa((int)$cliente['id']);

if ($activa) {
    $hoy = new DateTime('today');
    $fin = new DateTime($activa['fecha_fin']);
    $dias_restantes = max(0, (int)$hoy->diff($fin)->days);

    db()->prepare(
        "INSERT INTO registros_acceso (cliente_id, tipo, metodo, resultado)
         VALUES (:cid, :tipo, :metodo, 'permitido')"
    )->execute([':cid' => $cliente['id'], ':tipo' => $tipo, ':metodo' => $metodo]);

    echo json_encode([
        'ok'        => true,
        'permitido' => true,
        'cliente'   => ['id' => $cliente['id'], 'nombre' => $cliente['nombre']],
        'membresia' => [
            'nombre'          => $activa['membresia_nombre'],
            'fecha_fin'       => $activa['fecha_fin'],
            'dias_restantes'  => $dias_restantes,
        ],
        'mensaje' => 'Acceso permitido — ' . $activa['membresia_nombre'] . ' vence ' . date('d/m/Y', strtotime($activa['fecha_fin'])),
    ]);
} else {
    db()->prepare(
        "INSERT INTO registros_acceso (cliente_id, tipo, metodo, resultado, motivo)
         VALUES (:cid, :tipo, :metodo, 'denegado', 'Sin membresía activa')"
    )->execute([':cid' => $cliente['id'], ':tipo' => $tipo, ':metodo' => $metodo]);

    echo json_encode([
        'ok'        => true,
        'permitido' => false,
        'cliente'   => ['id' => $cliente['id'], 'nombre' => $cliente['nombre']],
        'mensaje'   => 'Acceso denegado — Sin membresía activa',
    ]);
}
