<?php
// ══════════════════════════════════════════════════
//  EgoGym — Funciones auxiliares globales
// ══════════════════════════════════════════════════

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// ── PDO Singleton ──────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Error de conexión a la base de datos.');
        }
    }
    return $pdo;
}

// ── Sanitización ───────────────────────────────────
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function clean(string $s): string {
    return trim(strip_tags($s));
}

// ── Redireccionamiento ─────────────────────────────
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

// ── Mensajes flash ─────────────────────────────────
function flash(string $key, string $message, string $type = 'info'): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
}

function get_flash(string $key): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function render_flash(string $key): void {
    $f = get_flash($key);
    if ($f) {
        echo '<div class="alert alert-' . h($f['type']) . '">' . h($f['message']) . '</div>';
    }
}

// ── CSRF ───────────────────────────────────────────
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_verify(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $_POST['csrf_token'] ?? '';
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Formateo ───────────────────────────────────────
function fmt_precio(float $monto): string {
    return '$' . number_format($monto, 2) . ' MXN';
}

function fmt_fecha(string $fecha): string {
    $d = new DateTime($fecha, new DateTimeZone(APP_TIMEZONE));
    return $d->format('d/m/Y');
}

function fmt_fecha_hora(string $fecha): string {
    $d = new DateTime($fecha, new DateTimeZone(APP_TIMEZONE));
    return $d->format('d/m/Y H:i');
}

function dias_texto(int $dias): string {
    return match(true) {
        $dias === 1   => '1 día',
        $dias <= 7    => $dias . ' días',
        $dias <= 31   => '1 mes',
        $dias <= 92   => '3 meses',
        $dias <= 186  => '6 meses',
        $dias <= 366  => '1 año',
        default       => $dias . ' días',
    };
}

// ── Membresía activa de un cliente ─────────────────
function membresia_activa(int $cliente_id): ?array {
    $sql = "SELECT c.*, m.nombre AS membresia_nombre, m.descripcion
            FROM compras c
            JOIN membresias m ON m.id = c.membresia_id
            WHERE c.cliente_id = :id
              AND c.status = 'completado'
              AND c.fecha_fin >= CURDATE()
            ORDER BY c.fecha_fin DESC
            LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute([':id' => $cliente_id]);
    return $stmt->fetch() ?: null;
}

// ── Verificar si email ya existe ───────────────────
function email_existe(string $email): bool {
    $stmt = db()->prepare("SELECT id FROM clientes WHERE email = :e LIMIT 1");
    $stmt->execute([':e' => $email]);
    return (bool) $stmt->fetch();
}
