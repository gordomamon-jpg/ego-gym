<?php
// ── Protección de rutas del panel admin ────────────
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['admin_id'])) {
    require_once __DIR__ . '/functions.php';
    flash('error', 'Acceso restringido. Inicia sesión como administrador.', 'error');
    redirect(APP_URL . '/admin/login.php');
}
