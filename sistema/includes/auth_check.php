<?php
// ── Protección de rutas del panel cliente ──────────
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['cliente_id'])) {
    require_once __DIR__ . '/functions.php';
    flash('error', 'Debes iniciar sesión para continuar.', 'error');
    redirect(APP_URL . '/cliente/login.php');
}
