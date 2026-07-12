<?php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['cliente_id'])) redirect(APP_URL . '/cliente/dashboard.php');

$errors = [];
$nombre = $email = $telefono = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Token de seguridad inválido. Recarga la página.';
    } else {
        $nombre    = clean($_POST['nombre']    ?? '');
        $email     = clean($_POST['email']     ?? '');
        $telefono  = clean($_POST['telefono']  ?? '');
        $password  = $_POST['password']        ?? '';
        $password2 = $_POST['password2']       ?? '';

        // Validaciones
        if (strlen($nombre) < 2 || strlen($nombre) > 120) {
            $errors[] = 'El nombre debe tener entre 2 y 120 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electrónico no es válido.';
        }
        if (email_existe($email)) {
            $errors[] = 'Este correo ya está registrado. ¿Quieres <a href="login.php">iniciar sesión</a>?';
        }
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $password2) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (empty($errors)) {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = db()->prepare(
                "INSERT INTO clientes (nombre, email, password_hash, telefono) VALUES (:n, :e, :h, :t)"
            );
            $stmt->execute([
                ':n' => $nombre,
                ':e' => $email,
                ':h' => $hash,
                ':t' => $telefono ?: null,
            ]);

            $id = (int) db()->lastInsertId();
            $_SESSION['cliente_id']     = $id;
            $_SESSION['cliente_nombre'] = $nombre;
            $_SESSION['cliente_email']  = $email;

            flash('success', '¡Bienvenido a EgoGym! Tu cuenta fue creada.', 'success');
            redirect(APP_URL . '/cliente/membresias.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Crear cuenta — EgoGym Fitness</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <a href="<?= APP_URL ?>"><em>EGOGYM</em> FITNESS</a>
    </div>

    <div class="auth-card">
      <h2>Crear cuenta</h2>
      <p class="subtitle">Únete y elige tu membresía</p>

      <?php if ($errors): ?>
        <div class="alert alert-error">
          <div>
            <?php foreach ($errors as $err): ?>
              <div><?= $err ?></div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <?php csrf_field(); ?>

        <div class="form-group">
          <label for="nombre">Nombre completo</label>
          <input type="text" id="nombre" name="nombre" class="form-control"
                 value="<?= h($nombre) ?>" placeholder="Tu nombre" required autocomplete="name"/>
        </div>

        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" name="email" class="form-control"
                 value="<?= h($email) ?>" placeholder="correo@ejemplo.com" required autocomplete="email"/>
        </div>

        <div class="form-group">
          <label for="telefono">Teléfono <span style="color:var(--muted);font-weight:400">(opcional)</span></label>
          <input type="tel" id="telefono" name="telefono" class="form-control"
                 value="<?= h($telefono) ?>" placeholder="55 1234 5678" autocomplete="tel"/>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Mínimo 8 caracteres" required autocomplete="new-password"/>
          <p class="form-hint">Al menos 8 caracteres</p>
        </div>

        <div class="form-group">
          <label for="password2">Confirmar contraseña</label>
          <input type="password" id="password2" name="password2" class="form-control"
                 placeholder="Repite tu contraseña" required autocomplete="new-password"/>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px">
          Crear cuenta
        </button>
      </form>
    </div>

    <p class="auth-footer">
      ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
    </p>
  </div>
</div>
</body>
</html>
