<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$id     = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$errors = [];
$datos  = ['nombre' => '', 'email' => '', 'telefono' => '', 'activo' => 1];

// Cargar datos si es edición
if ($id) {
    $stmt = db()->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        flash('error', 'Cliente no encontrado.', 'error');
        redirect(APP_URL . '/admin/clientes.php');
    }
    $datos = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Token inválido.';
    } else {
        $nombre   = clean($_POST['nombre']   ?? '');
        $email    = clean($_POST['email']    ?? '');
        $telefono = clean($_POST['telefono'] ?? '');
        $activo   = isset($_POST['activo']) ? 1 : 0;
        $password = $_POST['password'] ?? '';

        if (strlen($nombre) < 2)               $errors[] = 'El nombre debe tener al menos 2 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Correo inválido.';

        // Verificar email duplicado (excluyendo el propio si es edición)
        $chk = db()->prepare("SELECT id FROM clientes WHERE email = :e AND id != :id LIMIT 1");
        $chk->execute([':e' => $email, ':id' => $id ?? 0]);
        if ($chk->fetch()) $errors[] = 'Este correo ya está registrado.';

        if (!$id && strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        if ($id && $password !== '' && strlen($password) < 8) $errors[] = 'La nueva contraseña debe tener al menos 8 caracteres.';

        if (empty($errors)) {
            if ($id) {
                // Actualizar
                $sql = "UPDATE clientes SET nombre=:n, email=:e, telefono=:t, activo=:a WHERE id=:id";
                $params = [':n'=>$nombre,':e'=>$email,':t'=>$telefono?:null,':a'=>$activo,':id'=>$id];
                if ($password !== '') {
                    $sql = "UPDATE clientes SET nombre=:n, email=:e, telefono=:t, activo=:a, password_hash=:h WHERE id=:id";
                    $params[':h'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                }
                db()->prepare($sql)->execute($params);
                flash('success', 'Cliente actualizado correctamente.', 'success');
            } else {
                // Crear
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                db()->prepare(
                    "INSERT INTO clientes (nombre, email, password_hash, telefono, activo) VALUES (:n,:e,:h,:t,:a)"
                )->execute([':n'=>$nombre,':e'=>$email,':h'=>$hash,':t'=>$telefono?:null,':a'=>$activo]);
                $nuevo_id = (int)db()->lastInsertId();
                flash('success', 'Cliente creado. Ahora registra su huella dactilar.', 'success');
                redirect(APP_URL . '/admin/registrar_huella.php?cliente_id=' . $nuevo_id);
            }
            redirect(APP_URL . '/admin/cliente_detalle.php?id=' . $id);
        }

        $datos = array_merge($datos, compact('nombre','email','telefono','activo'));
    }
}

$titulo = $id ? 'Editar cliente' : 'Nuevo cliente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?= h($titulo) ?> — Admin EgoGym</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <div class="sidebar-overlay" id="overlay" onclick="closeSidebar()"></div>
  <div class="main">
    <?php include __DIR__ . '/partials/topbar.php'; ?>

    <div class="content">
      <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px">
        <div>
          <h1><?= h($titulo) ?></h1>
          <p><?= $id ? 'Modifica los datos del cliente' : 'Registra un nuevo cliente manualmente' ?></p>
        </div>
        <a href="<?= $id ? 'cliente_detalle.php?id='.$id : 'clientes.php' ?>" class="btn btn-ghost">← Volver</a>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom:20px">
          <?php foreach ($errors as $e): echo h($e) . '<br>'; endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="card" style="max-width:580px">
        <div class="card-body">
          <form method="POST" action="" novalidate>
            <?php csrf_field(); ?>

            <div class="form-group">
              <label for="nombre">Nombre completo</label>
              <input type="text" id="nombre" name="nombre" class="form-control"
                     value="<?= h($datos['nombre']) ?>" placeholder="Nombre completo" required/>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= h($datos['email']) ?>" placeholder="correo@ejemplo.com" required/>
              </div>
              <div class="form-group">
                <label for="telefono">Teléfono <span style="color:var(--muted);font-weight:400">(opcional)</span></label>
                <input type="tel" id="telefono" name="telefono" class="form-control"
                       value="<?= h($datos['telefono'] ?? '') ?>" placeholder="55 1234 5678"/>
              </div>
            </div>

            <div class="form-group">
              <label for="password">
                <?= $id ? 'Nueva contraseña' : 'Contraseña' ?>
                <?php if ($id): ?><span style="color:var(--muted);font-weight:400">(dejar vacío para no cambiar)</span><?php endif; ?>
              </label>
              <input type="password" id="password" name="password" class="form-control"
                     placeholder="<?= $id ? 'Dejar vacío para no cambiar' : 'Mínimo 8 caracteres' ?>"
                     <?= !$id ? 'required' : '' ?> autocomplete="new-password"/>
              <?php if (!$id): ?>
                <p class="form-hint">El cliente podrá cambiarla desde su panel.</p>
              <?php endif; ?>
            </div>

            <div class="form-group">
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;text-transform:none;letter-spacing:0;font-size:.88rem;color:var(--light)">
                <input type="checkbox" name="activo" value="1" <?= $datos['activo'] ? 'checked' : '' ?>
                       style="width:16px;height:16px;accent-color:var(--tq)"/>
                Cuenta activa (puede iniciar sesión)
              </label>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
              <button type="submit" class="btn btn-primary">
                <?= $id ? 'Guardar cambios' : 'Crear cliente' ?>
              </button>
              <a href="<?= $id ? 'cliente_detalle.php?id='.$id : 'clientes.php' ?>" class="btn btn-ghost">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('open');}
function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('overlay').classList.remove('open');}
</script>
</body>
</html>
