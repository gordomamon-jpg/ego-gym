<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$id      = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$errors  = [];
$memb    = [
    'nombre' => '', 'precio' => '', 'duracion_dias' => '',
    'descripcion' => '', 'beneficios' => '', 'activa' => 1, 'destacada' => 0,
    'stripe_price_id' => '',
];

// Cargar datos si es edición
if ($id) {
    $stmt = db()->prepare("SELECT * FROM membresias WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) { flash('error', 'Membresía no encontrada.', 'error'); redirect(APP_URL . '/admin/membresias.php'); }
    $memb = $row;
    // Convertir beneficios de JSON a texto línea por línea
    $bens = json_decode($memb['beneficios'] ?? '[]', true) ?: [];
    $memb['beneficios_text'] = implode("\n", $bens);
} else {
    $memb['beneficios_text'] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Token inválido.';
    } else {
        $nombre        = clean($_POST['nombre']         ?? '');
        $precio        = clean($_POST['precio']         ?? '');
        $duracion      = clean($_POST['duracion_dias']  ?? '');
        $descripcion   = clean($_POST['descripcion']    ?? '');
        $beneficios_raw= trim($_POST['beneficios']      ?? '');
        $activa        = isset($_POST['activa'])  ? 1 : 0;
        $destacada     = isset($_POST['destacada'])? 1 : 0;
        $stripe_pid    = clean($_POST['stripe_price_id'] ?? '');

        // Validaciones
        if ($nombre === '')             $errors[] = 'El nombre es requerido.';
        if (!is_numeric($precio) || (float)$precio < 0) $errors[] = 'El precio debe ser un número positivo.';
        if (!is_numeric($duracion) || (int)$duracion < 1) $errors[] = 'La duración debe ser al menos 1 día.';

        // Procesar beneficios (uno por línea → JSON array)
        $bens_array = array_filter(array_map('trim', explode("\n", $beneficios_raw)));
        $bens_json  = json_encode(array_values($bens_array), JSON_UNESCAPED_UNICODE);

        if (empty($errors)) {
            $data = [
                ':nombre'         => $nombre,
                ':precio'         => (float)$precio,
                ':duracion_dias'  => (int)$duracion,
                ':descripcion'    => $descripcion ?: null,
                ':beneficios'     => $bens_json,
                ':activa'         => $activa,
                ':destacada'      => $destacada,
                ':stripe_price_id'=> $stripe_pid ?: null,
            ];

            if ($id) {
                $sql = "UPDATE membresias SET
                          nombre = :nombre, precio = :precio, duracion_dias = :duracion_dias,
                          descripcion = :descripcion, beneficios = :beneficios,
                          activa = :activa, destacada = :destacada,
                          stripe_price_id = :stripe_price_id
                        WHERE id = :id";
                $data[':id'] = $id;
                db()->prepare($sql)->execute($data);
                flash('success', 'Membresía actualizada.', 'success');
            } else {
                $sql = "INSERT INTO membresias (nombre, precio, duracion_dias, descripcion, beneficios, activa, destacada, stripe_price_id)
                        VALUES (:nombre, :precio, :duracion_dias, :descripcion, :beneficios, :activa, :destacada, :stripe_price_id)";
                db()->prepare($sql)->execute($data);
                flash('success', 'Membresía creada exitosamente.', 'success');
            }
            redirect(APP_URL . '/admin/membresias.php');
        }

        // Repoblar
        $memb = array_merge($memb, [
            'nombre' => $nombre, 'precio' => $precio, 'duracion_dias' => $duracion,
            'descripcion' => $descripcion, 'activa' => $activa, 'destacada' => $destacada,
            'stripe_price_id' => $stripe_pid, 'beneficios_text' => $beneficios_raw,
        ]);
    }
}

$titulo = $id ? 'Editar membresía' : 'Nueva membresía';
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
          <p>Configura los detalles del plan</p>
        </div>
        <a href="membresias.php" class="btn btn-ghost">← Volver</a>
      </div>

      <?php if ($errors): ?>
        <div class="alert alert-error" style="margin-bottom:20px">
          <?php foreach ($errors as $e): echo h($e) . '<br>'; endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="card" style="max-width:640px">
        <div class="card-body">
          <form method="POST" action="" novalidate>
            <?php csrf_field(); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
              <div class="form-group" style="grid-column:1/-1">
                <label for="nombre">Nombre de la membresía</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="<?= h($memb['nombre']) ?>" placeholder="Ej: Mensual" required/>
              </div>

              <div class="form-group">
                <label for="precio">Precio (MXN)</label>
                <input type="number" id="precio" name="precio" class="form-control"
                       value="<?= h($memb['precio']) ?>" placeholder="550.00"
                       step="0.01" min="0" required/>
              </div>

              <div class="form-group">
                <label for="duracion_dias">Duración (días)</label>
                <input type="number" id="duracion_dias" name="duracion_dias" class="form-control"
                       value="<?= h($memb['duracion_dias']) ?>" placeholder="30"
                       min="1" required/>
              </div>

              <div class="form-group" style="grid-column:1/-1">
                <label for="descripcion">Descripción breve</label>
                <input type="text" id="descripcion" name="descripcion" class="form-control"
                       value="<?= h($memb['descripcion'] ?? '') ?>"
                       placeholder="La membresía más popular"/>
              </div>

              <div class="form-group" style="grid-column:1/-1">
                <label for="beneficios">Beneficios <span style="color:var(--muted);font-weight:400">(uno por línea)</span></label>
                <textarea id="beneficios" name="beneficios" class="form-control" rows="5"
                          placeholder="Acceso ilimitado&#10;Uso de vestidores&#10;Asesoría nutricional"><?= h($memb['beneficios_text'] ?? '') ?></textarea>
              </div>

              <div class="form-group">
                <label for="stripe_price_id">Stripe Price ID <span style="color:var(--muted);font-weight:400">(opcional)</span></label>
                <input type="text" id="stripe_price_id" name="stripe_price_id" class="form-control"
                       value="<?= h($memb['stripe_price_id'] ?? '') ?>" placeholder="price_xxx"/>
              </div>

              <div class="form-group" style="display:flex;flex-direction:column;justify-content:flex-end;gap:12px">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.88rem;color:var(--light);text-transform:none;letter-spacing:0">
                  <input type="checkbox" name="activa" value="1" <?= $memb['activa'] ? 'checked' : '' ?>
                         style="width:16px;height:16px;accent-color:var(--tq)"/>
                  Membresía activa (visible)
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.88rem;color:var(--light);text-transform:none;letter-spacing:0">
                  <input type="checkbox" name="destacada" value="1" <?= $memb['destacada'] ? 'checked' : '' ?>
                         style="width:16px;height:16px;accent-color:var(--pu)"/>
                  Mostrar como destacada
                </label>
              </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
              <button type="submit" class="btn btn-primary">
                <?= $id ? 'Guardar cambios' : 'Crear membresía' ?>
              </button>
              <a href="membresias.php" class="btn btn-ghost">Cancelar</a>
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
