<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

// Búsqueda y paginación
$buscar   = clean($_GET['q'] ?? '');
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = 25;
$offset   = ($pagina - 1) * $por_pag;
$params   = [];
$where    = "WHERE 1=1";

if ($buscar !== '') {
    $where .= " AND (cl.nombre LIKE :q OR cl.email LIKE :q)";
    $params[':q'] = "%$buscar%";
}

// Total para paginación
$total_stmt = db()->prepare("SELECT COUNT(*) FROM clientes cl $where");
$total_stmt->execute($params);
$total_registros = (int)$total_stmt->fetchColumn();
$total_paginas   = max(1, (int)ceil($total_registros / $por_pag));
$pagina          = min($pagina, $total_paginas);

$params_pag = $params;
$params_pag[':limit']  = $por_pag;
$params_pag[':offset'] = $offset;

$clientes = db()->prepare(
    "SELECT cl.*,
            (SELECT COUNT(*) FROM compras c WHERE c.cliente_id = cl.id AND c.status = 'completado' AND c.fecha_fin >= CURDATE()) AS membresia_activa,
            (SELECT m.nombre FROM compras c JOIN membresias m ON m.id = c.membresia_id WHERE c.cliente_id = cl.id AND c.status = 'completado' AND c.fecha_fin >= CURDATE() ORDER BY c.fecha_fin DESC LIMIT 1) AS membresia_nombre,
            (SELECT c.fecha_fin FROM compras c WHERE c.cliente_id = cl.id AND c.status = 'completado' AND c.fecha_fin >= CURDATE() ORDER BY c.fecha_fin DESC LIMIT 1) AS fecha_vencimiento,
            (SELECT COUNT(*) FROM compras c WHERE c.cliente_id = cl.id AND c.status = 'completado') AS total_compras
     FROM clientes cl $where
     ORDER BY cl.created_at DESC
     LIMIT :limit OFFSET :offset"
);
$clientes->bindValue(':limit',  $por_pag, PDO::PARAM_INT);
$clientes->bindValue(':offset', $offset,  PDO::PARAM_INT);
if ($buscar !== '') $clientes->bindValue(':q', "%$buscar%");
$clientes->execute();
$lista = $clientes->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Clientes — Admin EgoGym</title>
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
          <h1>Clientes</h1>
          <p><?= $total_registros ?> cliente<?= $total_registros !== 1 ? 's' : '' ?> · página <?= $pagina ?> de <?= $total_paginas ?></p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <form method="GET" style="display:flex;gap:8px">
            <input type="text" name="q" value="<?= h($buscar) ?>"
                   placeholder="Buscar por nombre o email..."
                   class="form-control" style="width:240px"/>
            <button type="submit" class="btn btn-ghost">Buscar</button>
            <?php if ($buscar): ?>
              <a href="clientes.php" class="btn btn-danger btn-sm">✕</a>
            <?php endif; ?>
          </form>
          <a href="cliente_form.php" class="btn btn-primary">+ Nuevo cliente</a>
        </div>
      </div>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Membresía activa</th>
                <th>Vencimiento</th>
                <th>Compras</th>
                <th>Registro</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($lista): foreach ($lista as $c): ?>
                <tr>
                  <td style="color:var(--muted)"><?= $c['id'] ?></td>
                  <td>
                    <a href="cliente_detalle.php?id=<?= $c['id'] ?>" style="font-weight:600;color:var(--white)"><?= h($c['nombre']) ?></a>
                    <div style="font-size:.78rem;color:var(--muted)"><?= h($c['email']) ?></div>
                    <?php if ($c['telefono']): ?>
                      <div style="font-size:.76rem;color:var(--muted)"><?= h($c['telefono']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($c['membresia_activa']): ?>
                      <span class="badge badge-success"><?= h($c['membresia_nombre']) ?></span>
                    <?php else: ?>
                      <span class="badge badge-muted">Sin membresía</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?= $c['fecha_vencimiento'] ? fmt_fecha($c['fecha_vencimiento']) : '—' ?>
                  </td>
                  <td style="text-align:center"><?= (int)$c['total_compras'] ?></td>
                  <td><?= fmt_fecha($c['created_at']) ?></td>
                  <td>
                    <span class="badge <?= $c['activo'] ? 'badge-success' : 'badge-danger' ?>">
                      <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
                    </span>
                  </td>
                  <td>
                    <div style="display:flex;gap:5px;flex-wrap:wrap">
                      <a href="cliente_detalle.php?id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">Ver</a>
                      <a href="asignar_plan.php?cliente_id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Plan</a>
                      <a href="accesos.php?registrar=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">Acceso</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:32px">No se encontraron clientes</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
          <!-- Paginación -->
          <?php if ($total_paginas > 1): ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-top:1px solid var(--border);flex-wrap:wrap;gap:10px">
            <span style="font-size:.82rem;color:var(--muted)">
              Mostrando <?= ($offset + 1) ?>–<?= min($offset + $por_pag, $total_registros) ?> de <?= $total_registros ?>
            </span>
            <div style="display:flex;gap:4px">
              <?php
              $qs = $buscar ? '&q=' . urlencode($buscar) : '';
              $prev = $pagina - 1; $next = $pagina + 1;
              ?>
              <a href="?p=1<?= $qs ?>" class="btn btn-ghost btn-sm" <?= $pagina <= 1 ? 'style="opacity:.35;pointer-events:none"' : '' ?>>«</a>
              <a href="?p=<?= $prev ?><?= $qs ?>" class="btn btn-ghost btn-sm" <?= $pagina <= 1 ? 'style="opacity:.35;pointer-events:none"' : '' ?>>‹ Ant</a>
              <?php for ($i = max(1, $pagina-2); $i <= min($total_paginas, $pagina+2); $i++): ?>
                <a href="?p=<?= $i ?><?= $qs ?>" class="btn btn-sm <?= $i === $pagina ? 'btn-primary' : 'btn-ghost' ?>"><?= $i ?></a>
              <?php endfor; ?>
              <a href="?p=<?= $next ?><?= $qs ?>" class="btn btn-ghost btn-sm" <?= $pagina >= $total_paginas ? 'style="opacity:.35;pointer-events:none"' : '' ?>>Sig ›</a>
              <a href="?p=<?= $total_paginas ?><?= $qs ?>" class="btn btn-ghost btn-sm" <?= $pagina >= $total_paginas ? 'style="opacity:.35;pointer-events:none"' : '' ?>>»</a>
            </div>
          </div>
          <?php endif; ?>
          <div style="display:none">
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
