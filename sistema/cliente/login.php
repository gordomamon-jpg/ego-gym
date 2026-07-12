<?php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['cliente_id'])) redirect(APP_URL . '/cliente/dashboard.php');

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Token inválido. Recarga la página.';
    } else {
        $email    = clean($_POST['email']    ?? '');
        $password = $_POST['password']       ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Correo inválido.';
        }

        if (empty($errors)) {
            $stmt = db()->prepare("SELECT * FROM clientes WHERE email = :e AND activo = 1 LIMIT 1");
            $stmt->execute([':e' => $email]);
            $cliente = $stmt->fetch();

            if (!$cliente || !password_verify($password, $cliente['password_hash'])) {
                $errors[] = 'Correo o contraseña incorrectos.';
            } else {
                $_SESSION['cliente_id']     = $cliente['id'];
                $_SESSION['cliente_nombre'] = $cliente['nombre'];
                $_SESSION['cliente_email']  = $cliente['email'];

                redirect(APP_URL . '/cliente/dashboard.php');
            }
        }
    }
}

$flash = get_flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Iniciar sesión — EgoGym Fitness</title>
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
      <h2>Iniciar sesión</h2>
      <p class="subtitle">Accede a tu membresía y cuenta</p>

      <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err): ?>
            <div><?= h($err) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <?php csrf_field(); ?>

        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input type="email" id="email" name="email" class="form-control"
                 value="<?= h($email) ?>" placeholder="correo@ejemplo.com"
                 required autocomplete="email"/>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Tu contraseña" required autocomplete="current-password"/>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px">
          Entrar
        </button>
      </form>
    </div>

    <p class="auth-footer">
      ¿No tienes cuenta? <a href="registro.php">Regístrate gratis</a>
    </p>
  </div>
</div>
</body>
</html>
