<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Pago cancelado — EgoGym Fitness</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/sistema.css"/>
</head>
<body>
<div class="auth-page">
  <div class="auth-box" style="max-width:420px">
    <div class="auth-logo">
      <a href="../../index.php"><em>EGOGYM</em> FITNESS</a>
    </div>
    <div class="auth-card" style="text-align:center">
      <div style="display:flex;justify-content:center;margin-bottom:20px">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--warn-lo);border:1px solid rgba(255,180,67,.3);display:flex;align-items:center;justify-content:center">
          <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="var(--warn)" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </div>
      </div>
      <h2 style="margin-bottom:8px">Pago cancelado</h2>
      <p style="color:var(--muted);margin-bottom:24px">No se realizó ningún cargo. Puedes intentarlo de nuevo cuando quieras.</p>
      <a href="membresias.php" class="btn btn-primary btn-full">Ver membresías</a>
      <a href="dashboard.php" class="btn btn-ghost btn-full" style="margin-top:10px">Volver a mi cuenta</a>
    </div>
  </div>
</div>
</body>
</html>
