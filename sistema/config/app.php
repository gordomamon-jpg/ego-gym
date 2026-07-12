<?php
// ══════════════════════════════════════════════════
//  EgoGym — Configuración general de la aplicación
//  Copia este archivo como referencia; los reales
//  están en .gitignore.
// ══════════════════════════════════════════════════

define('APP_NAME',     'EgoGym Fitness');
define('APP_URL',      'http://localhost/ego-gym/sistema'); // Sin / al final
define('APP_TIMEZONE', 'America/Mexico_City');
define('APP_ENV',      'development');          // 'production' en VPS

// API Key para lector de huella / dispositivos externos
// Cambiar por una clave segura aleatoria antes de usar en producción
define('EGOGYM_API_KEY', 'egogym_api_key_2024_cambiar_en_produccion');

date_default_timezone_set(APP_TIMEZONE);

// Sesiones seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (APP_ENV === 'production') {
    ini_set('session.cookie_secure', 1);
}
