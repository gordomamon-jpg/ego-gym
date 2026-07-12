<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

// Filtros y paginación
$status   = clean($_GET['status']   ?? '');
$buscar   = clean($_GET['q']        ?? '');
$mes      = clean($_GET['mes']      ?? '');
$pagina   = max(1, (int)($_GET['p'] ?? 1));
$por_pag  = 30;
$offset   = ($pagina - 1) * $por_pag;

$where  = "WHERE 1=1";
$params = [];

if ($status !== '') {
    $where .= " AND c.status = :status";
    $params[':status'] = $status;
}
if ($buscar !== '') {
    $where .= " AND (cl.nombre LIKE :q OR cl.email LIKE :q)";
    $params[':q'] = "%$buscar%";
}
if ($mes !== '' && preg_match('/^\d{4}-\d{2}$/', $mes)) {
    $where .= " AND DATE_FORMAT(c.created_at, '%Y-%m') = :mes";
    $params[':mes'] = $mes;
}

// Total para paginación + suma de completados
$total_stmt = db()->prepare(
    "SELECT COUNT(*), COALESCE(SUM(CASE WHEN c.status='completado' THEN c.monto ELSE 0 END),0)
     FROM compras c JOIN clientes cl ON cl.id=c.cliente_id $where"
);
$total_stmt->execute($params);
[$total_registros, $total_filtrado] = $total_stmt->fetch(PDO::FETCH_NUM);
$total_paginas = max(1, (int)ceil($total_registros / $por_pag));
$pagina        = min($pagina, $total_paginas);

$compras = db()->prepare(
    "SELECT c.*, cl.nombre AS cliente_nombre, cl.email AS cliente_email,
            m.nombre AS membresia_nombre
     FROM compras c
     JOIN clientes cl ON cl.id = c.cliente_id
     JOIN membresias m ON m.id = c.membresia_id
     $where
     ORDER BY c.created_at DESC
     LIMIT :limit OFFSET :offset"
);
foreach ($params as $k => $v) $compras->bindValue($k, $v);
$compras->bindValue(':limit',  $por_pag, PDO::PARAM_INT);
$compras->bindValue(':offset', $offset,  PDO::PARAM_INT);
$compras->execute();
$lista = $compras->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Pagos — Admin EgoGym</title>
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
          <h1>Pagos</h1>
          <p>
            <?= $total_registros ?> registro<?= $total_registros != 1 ? 's' : '' ?> · página <?= $pagina ?> de <?= $total_paginas ?>
            <?php if ($total_filtrado > 0): ?>
              · <strong style="color:var(--tq)"><?= fmt_precio((float)$total_filtrado) ?></strong> completados
            <?php endif; ?>
          </p>
        </div>
      </div>

      <!-- Filtros -->
      <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px">
        <input type="text" name="q" value="<?= h($buscar) ?>"
               placeholder="Buscar cliente..." class="form-control" style="width:220px"/>

        <select name="status" class="form-control" style="width:160px">
          <option value="">Todos los estados</option>
          <?php foreach (['completado','pendiente','fallido','reembolsado'] as $s): ?>
            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>

        <input type="month" name="mes" value="<?= h($mes) ?>" class="form-control" style="width:160px"/>

        <button type="submit" class="btn btn-ghost">Filtrar</button>
        <?php if ($buscar || $status || $mes): ?>
          <a href="pagos.php" class="btn btn-danger btn-sm">✕ Limpiar</a>
        <?php endif; ?>
      </form>

      <div class="card">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Membresía</th>
                <th>Monto</th>
                <th>Fecha pago</th>
                <th>Inicio</th>
                <th>Vencimiento</th>
                <th>Estado</th>
                <th>Stripe ID</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($lista): foreach ($lista as $c): ?>
              <tr>
                  <td style="color:var(--muted)"><?= $c['id'] ?></td>
                  <td>
                    <div style="font-weight:600;color:var(--white)"><?= h($c['cliente_nombre']) ?></div>
                    <div style="font-size:.78rem;color:var(--muted)"><?= h($c['cliente_email']) ?></div>
                  </td>
                  <td><?= h($c['membresia_nombre']) ?></td>
                  <td style="color:var(--tq);font-weight:700"><?= fmt_precio((float)$c['monto']) ?></td>
                  <td><?= $c['fecha_pago'] ? fmt_fecha_hora($c['fecha_pago']) : '—' ?></td>
                  <td><?= fmt_fecha($c['fecha_inicio']) ?></td>
                  <td><?= fmt_fecha($c['fecha_fin']) ?></td>
                  <td>
                    <?php $badges = ['completado'=>'badge-success','pendiente'=>'badge-warn','fallido'=>'badge-danger','reembolsado'=>'badge-muted']; ?>
                    <span class="badge <?= $badges[$c['status']] ?? 'badge-muted' ?>"><?= h(ucfirst($c['status'])) ?></span>
                  </td>
                  <td style="font-size:.76rem;color:var(--muted);max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <abbr title="<?= h($c['stripe_payment_id'] ?? '') ?>"><?= $c['stripe_payment_id'] ? substr($c['stripe_payment_id'],0,18).'...' : '—' ?></abbr>
                  </td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:32px">No hay registros</td></tr>
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
              $qs = http_build_query(array_filter(['q'=>$buscar,'status'=>$status,'mes'=>$mes]));
              $qs = $qs ? '&'.$qs : '';
              ?>
              <a href="?p=1<?= $qs ?>" class="btn btn-ghost btn-sm" <?= $pagina<=1?'style="opacity:.35;pointer-events:none"':'' ?>>«</a>
              <a href="?p=<?= $pagina-1 ?><?= $qs ?>" class="btn btn-ghost btn-sm" <?= $pagina<=1?'style="opacity:.35;pointer-events:none"':'' ?>>‹ Ant</a>
              <?php for($i=max(1,$pagina-2);$i<=min($total_paginas,$pagina+2);$i++): ?>
                <a href="?p=<?= $i ?><?= $qs ?>" class="btn btn-sm <?= $i===$pagina?'btn-primary':'btn-ghost' ?>"><?= $i ?></a>
              <?php endfor; ?>
              <a href="?p=<?= $pagina+1 ?><?= $qs ?>" class="btn btn-ghost btn-sm" <?= $pagina>=$total_paginas?'style="opacity:.35;pointer-events:none"':'' ?>>Sig ›</a>
              <a href="?p=<?= $total_paginas ?><?= $qs ?>" class="btn btn-ghost btn-sm" <?= $pagina>=$total_paginas?'style="opacity:.35;pointer-events:none"':'' ?>>»</a>
            </div>
          </div>
          <?php endif; ?>
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
