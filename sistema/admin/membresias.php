<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

// Toggle activo/inactivo
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    if (!isset($_GET['csrf']) || !hash_equals(csrf_token(), $_GET['csrf'])) {
        flash('error', 'Token inválido.', 'error');
        redirect(APP_URL . '/admin/membresias.php');
    }
    $id = (int)$_GET['toggle'];
    db()->prepare("UPDATE membresias SET activa = NOT activa WHERE id = :id")
        ->execute([':id' => $id]);
    flash('success', 'Membresía actualizada.', 'success');
    redirect(APP_URL . '/admin/membresias.php');
}

$membresias = db()->query("SELECT * FROM membresias ORDER BY precio ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Membresías — Admin EgoGym</title>
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
          <h1>Membresías</h1>
          <p>Gestión de planes disponibles</p>
        </div>
        <a href="membresia_form.php" class="btn btn-primary">+ Nueva membresía</a>
      </div>

      <?php render_flash('success'); render_flash('error'); ?>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Duración</th>
                <th>Descripción</th>
                <th>Destacada</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($membresias as $m): ?>
                <tr>
                  <td style="color:var(--muted)"><?= $m['id'] ?></td>
                  <td style="font-weight:600;color:var(--white)"><?= h($m['nombre']) ?></td>
                  <td style="color:var(--tq);font-weight:700"><?= fmt_precio((float)$m['precio']) ?></td>
                  <td><?= h(dias_texto((int)$m['duracion_dias'])) ?></td>
                  <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--muted)">
                    <?= h($m['descripcion'] ?? '—') ?>
                  </td>
                  <td>
                    <?= $m['destacada'] ? '<span class="badge badge-pu">Sí</span>' : '<span class="badge badge-muted">No</span>' ?>
                  </td>
                  <td>
                    <?= $m['activa']
                      ? '<span class="badge badge-success">Activa</span>'
                      : '<span class="badge badge-danger">Inactiva</span>'
                    ?>
                  </td>
                  <td>
                    <div style="display:flex;gap:6px">
                      <a href="membresia_form.php?id=<?= $m['id'] ?>" class="btn btn-ghost btn-sm">Editar</a>
                      <a href="membresias.php?toggle=<?= $m['id'] ?>&csrf=<?= h(csrf_token()) ?>"
                         class="btn btn-sm <?= $m['activa'] ? 'btn-danger' : 'btn-outline' ?>"
                         onclick="return confirm('¿Confirmar cambio de estado?')">
                        <?= $m['activa'] ? 'Desactivar' : 'Activar' ?>
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
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
