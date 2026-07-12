<?php
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['admin_id'])) redirect(APP_URL . '/admin/dashboard.php');

$errors = [];
$usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Token inválido. Recarga la página.';
    } else {
        $usuario  = clean($_POST['usuario']  ?? '');
        $password = $_POST['password']       ?? '';

        if ($usuario === '' || $password === '') {
            $errors[] = 'Completa todos los campos.';
        } else {
            $stmt = db()->prepare(
                "SELECT * FROM usuarios_admin WHERE usuario = :u AND activo = 1 LIMIT 1"
            );
            $stmt->execute([':u' => $usuario]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($password, $admin['password_hash'])) {
                $errors[] = 'Credenciales incorrectas.';
            } else {
                // Actualizar last_login
                db()->prepare("UPDATE usuarios_admin SET last_login = NOW() WHERE id = :id")
                    ->execute([':id' => $admin['id']]);

                $_SESSION['admin_id']     = $admin['id'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                $_SESSION['admin_usuario']= $admin['usuario'];

                redirect(APP_URL . '/admin/dashboard.php');
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
<title>Admin — EgoGym Fitness</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <a href="../../index.php"><em>EGOGYM</em> FITNESS</a>
    </div>
    <div class="auth-card">
      <h2>Panel Admin</h2>
      <p class="subtitle">Acceso restringido al personal</p>

      <?php if ($flash): ?>
        <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $e): echo h($e) . '<br>'; endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <?php csrf_field(); ?>

        <div class="form-group">
          <label for="usuario">Usuario</label>
          <input type="text" id="usuario" name="usuario" class="form-control"
                 value="<?= h($usuario) ?>" placeholder="admin" required autocomplete="username"/>
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Contraseña" required autocomplete="current-password"/>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px">
          Entrar
        </button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
