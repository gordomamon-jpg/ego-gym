<?php
/**
 * EgoGym — Generador de hash para contraseña de admin
 * IMPORTANTE: Eliminar o proteger este archivo en producción.
 *
 * Uso: abre este script en el navegador o PHP CLI:
 *   php generar_hash.php
 */

$password = 'Pass2024!';  // <-- cambia esto antes de ejecutar

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "Contraseña: $password\n";
echo "Hash:       $hash\n\n";
echo "SQL:\n";
echo "UPDATE usuarios_admin SET password_hash = '$hash' WHERE usuario = 'admin';\n";
