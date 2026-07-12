<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Control de Acceso — EgoGym Fitness</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
/* ── Tokens ── */
:root {
  --tq:      #38E0D0;
  --pu:      #7A3CFF;
  --bg:      #0B0B0F;
  --border:  rgba(255,255,255,.08);
  --success: #38E0A0;
  --danger:  #FF4D6A;
  --warn:    #FFB443;
  --muted:   #6A6A90;
  --white:   #fff;
  --light:   #D4D4F0;
  --fh: 'Barlow Condensed', sans-serif;
  --fb: 'Barlow', sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
  height: 100%; width: 100%;
  font-family: var(--fb);
  background: var(--bg);
  color: var(--white);
  overflow: hidden;
  -webkit-font-smoothing: antialiased;
}

/* ── KIOSKO ── */
.kiosko {
  width: 100vw; height: 100vh;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  position: relative;
  transition: background .6s ease;
}

/* Fondos por estado */
.kiosko.state-waiting  { background: var(--bg); }
.kiosko.state-reading  { background: var(--bg); }
.kiosko.state-permitido{ background: radial-gradient(ellipse 80% 70% at 50% 50%, rgba(56,224,160,.18) 0%, #0B0F0C 70%); }
.kiosko.state-denegado { background: radial-gradient(ellipse 80% 70% at 50% 50%, rgba(255,77,106,.18) 0%, #0F0B0C 70%); }

/* ── TOP BAR ── */
.k-topbar {
  position: fixed; top: 0; left: 0; right: 0;
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 28px;
  background: rgba(11,11,15,.7);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border);
  z-index: 10;
}
.k-logo {
  font-family: var(--fh); font-weight: 900; font-size: 1.1rem;
  letter-spacing: 2px; text-transform: uppercase;
}
.k-logo em { font-style: normal; background: linear-gradient(90deg,var(--tq),var(--pu)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.k-topbar-right { display: flex; align-items: center; gap: 16px; }
.k-time { font-family: var(--fh); font-weight: 700; font-size: 1.1rem; color: var(--light); letter-spacing: 1px; }
.k-back { color: var(--muted); font-size: .78rem; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; text-decoration: none; transition: color .2s; }
.k-back:hover { color: var(--tq); }

/* ── ICONO CENTRAL ── */
.k-icon-wrap {
  position: relative;
  margin-bottom: 36px;
  transition: transform .5s cubic-bezier(.34,1.56,.64,1);
}
.k-icon-wrap.pop { animation: pop .4s cubic-bezier(.34,1.56,.64,1); }
@keyframes pop { 0%{transform:scale(.7)} 70%{transform:scale(1.12)} 100%{transform:scale(1)} }

.k-icon {
  width: 160px; height: 160px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  transition: background .5s, box-shadow .5s;
}
.state-waiting  .k-icon { background: rgba(56,224,208,.08); box-shadow: 0 0 0 1px rgba(56,224,208,.18), 0 0 60px rgba(56,224,208,.08); }
.state-reading  .k-icon { background: rgba(56,224,208,.1);  box-shadow: 0 0 0 1px rgba(56,224,208,.25); animation: pulse-icon 1s ease-in-out infinite; }
.state-permitido .k-icon { background: rgba(56,224,160,.15); box-shadow: 0 0 0 2px rgba(56,224,160,.35), 0 0 80px rgba(56,224,160,.25); }
.state-denegado  .k-icon { background: rgba(255,77,106,.15); box-shadow: 0 0 0 2px rgba(255,77,106,.35), 0 0 80px rgba(255,77,106,.25); }

@keyframes pulse-icon {
  0%,100% { box-shadow: 0 0 0 1px rgba(56,224,208,.25), 0 0 40px rgba(56,224,208,.1); }
  50%      { box-shadow: 0 0 0 3px rgba(56,224,208,.4),  0 0 80px rgba(56,224,208,.25); }
}

/* Anillos pulsantes en espera */
.k-rings {
  position: absolute; inset: -20px;
  border-radius: 50%;
  pointer-events: none;
}
.k-ring {
  position: absolute; inset: 0;
  border-radius: 50%;
  border: 1px solid rgba(56,224,208,.15);
  animation: ring-pulse 3s ease-out infinite;
}
.k-ring:nth-child(2) { animation-delay: 1s; }
.k-ring:nth-child(3) { animation-delay: 2s; }
@keyframes ring-pulse {
  0%   { transform: scale(1);   opacity: .5; }
  100% { transform: scale(1.7); opacity: 0; }
}
.state-permitido .k-ring,
.state-denegado  .k-ring { animation: none; opacity: 0; }

/* ── TEXTO ── */
.k-status {
  font-family: var(--fh); font-weight: 900;
  font-size: clamp(2rem, 6vw, 4.5rem);
  text-transform: uppercase; letter-spacing: 2px;
  text-align: center; line-height: 1;
  margin-bottom: 16px;
  transition: color .4s;
}
.state-waiting  .k-status { color: var(--light); }
.state-reading  .k-status { color: var(--tq); }
.state-permitido .k-status { color: var(--success); }
.state-denegado  .k-status { color: var(--danger); }

.k-nombre {
  font-family: var(--fh); font-weight: 900;
  font-size: clamp(2.5rem, 7vw, 6rem);
  text-transform: uppercase; letter-spacing: -1px;
  text-align: center; line-height: 1;
  color: var(--white);
  margin-bottom: 14px;
  animation: fadeUp .4s ease;
}
@keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:none; } }

.k-membresia {
  font-family: var(--fh); font-weight: 600;
  font-size: clamp(1.1rem, 2.5vw, 1.8rem);
  text-transform: uppercase; letter-spacing: 2px;
  text-align: center;
  color: var(--muted);
  margin-bottom: 8px;
  animation: fadeUp .4s ease .1s both;
}
.k-dias {
  font-family: var(--fh); font-weight: 800;
  font-size: clamp(1rem, 2vw, 1.4rem);
  text-align: center; letter-spacing: 1px;
  text-transform: uppercase;
  animation: fadeUp .4s ease .15s both;
}
.state-permitido .k-dias { color: var(--success); }
.state-denegado  .k-dias { color: var(--danger); }

.k-sub {
  font-size: clamp(.85rem, 1.5vw, 1.05rem);
  color: var(--muted); text-align: center;
  letter-spacing: 2px; text-transform: uppercase;
  margin-top: 8px;
}

/* ── COUNTDOWN ── */
.k-countdown {
  position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%);
  display: flex; align-items: center; gap: 14px;
  opacity: 0; transition: opacity .3s;
}
.k-countdown.visible { opacity: 1; }
.k-cd-ring {
  width: 56px; height: 56px; position: relative;
}
.k-cd-ring svg { transform: rotate(-90deg); }
.k-cd-ring circle {
  fill: none; stroke-width: 3;
  stroke-linecap: round;
  transition: stroke-dashoffset 1s linear;
}
.k-cd-ring .bg  { stroke: rgba(255,255,255,.08); stroke-dasharray: 157; stroke-dashoffset: 0; }
.k-cd-ring .fg  { stroke: var(--tq); stroke-dasharray: 157; }
.k-cd-num {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--fh); font-weight: 900; font-size: 1.2rem; color: var(--tq);
}
.k-countdown-label { font-size: .78rem; color: var(--muted); letter-spacing: 1px; text-transform: uppercase; }

/* ── INPUT DE PRUEBA ── */
.k-dev-panel {
  position: fixed; bottom: 28px; right: 28px;
  background: rgba(20,20,25,.9); border: 1px solid var(--border);
  border-radius: 12px; padding: 14px 16px;
  backdrop-filter: blur(12px);
  display: flex; flex-direction: column; gap: 8px;
  z-index: 20; min-width: 280px;
}
.k-dev-title { font-size: .68rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--muted); margin-bottom: 2px; }
.k-dev-row { display: flex; gap: 6px; }
.k-dev-input {
  flex: 1; background: rgba(255,255,255,.05); border: 1px solid var(--border);
  border-radius: 8px; color: var(--white); font-family: monospace; font-size: .78rem;
  padding: 8px 10px; outline: none; transition: border-color .2s;
}
.k-dev-input:focus { border-color: rgba(56,224,208,.4); }
.k-dev-btn {
  background: linear-gradient(90deg,var(--tq),var(--pu)); color: #0B0B0F;
  border: none; border-radius: 8px; font-weight: 800; font-size: .75rem;
  letter-spacing: 1px; text-transform: uppercase; padding: 8px 14px; cursor: pointer; white-space: nowrap;
}
.k-dev-btn:hover { filter: brightness(1.1); }
.k-dev-hint { font-size: .66rem; color: var(--muted); text-align: center; }

/* ── SPINNER ── */
.spinner {
  width: 64px; height: 64px;
  border: 3px solid rgba(56,224,208,.15);
  border-top-color: var(--tq);
  border-radius: 50%;
  animation: spin .8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="k-topbar">
  <div class="k-logo"><em>EGOGYM</em> FITNESS</div>
  <div class="k-topbar-right">
    <span class="k-time" id="k-clock">--:--</span>
    <a href="dashboard.php" class="k-back">← Panel admin</a>
  </div>
</div>

<!-- KIOSKO PRINCIPAL -->
<div class="kiosko state-waiting" id="kiosko">

  <!-- Icono central -->
  <div class="k-icon-wrap" id="k-icon-wrap">
    <div class="k-rings" id="k-rings">
      <div class="k-ring"></div>
      <div class="k-ring"></div>
      <div class="k-ring"></div>
    </div>
    <div class="k-icon" id="k-icon">
      <!-- Waiting: huella -->
      <svg id="svg-waiting" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--tq)" stroke-width="1.3" stroke-linecap="round">
        <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
      </svg>
      <!-- Reading: spinner -->
      <div class="spinner" id="svg-reading" style="display:none"></div>
      <!-- Permitido: check -->
      <svg id="svg-permitido" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
        <path d="M20 6L9 17l-5-5"/>
      </svg>
      <!-- Denegado: X -->
      <svg id="svg-denegado" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none">
        <path d="M18 6L6 18M6 6l12 12"/>
      </svg>
    </div>
  </div>

  <!-- Textos dinámicos -->
  <div class="k-status" id="k-status">COLOCA TU DEDO EN EL LECTOR</div>
  <div class="k-nombre"  id="k-nombre"  style="display:none"></div>
  <div class="k-membresia" id="k-membresia" style="display:none"></div>
  <div class="k-dias"    id="k-dias"    style="display:none"></div>
  <div class="k-sub"     id="k-sub">EgoGym Fitness · Control de Acceso</div>

</div>

<!-- COUNTDOWN -->
<div class="k-countdown" id="k-countdown">
  <div class="k-cd-ring">
    <svg width="56" height="56" viewBox="0 0 56 56">
      <circle class="bg" cx="28" cy="28" r="25"/>
      <circle class="fg" id="cd-circle" cx="28" cy="28" r="25" stroke-dashoffset="0"/>
    </svg>
    <div class="k-cd-num" id="cd-num">5</div>
  </div>
  <span class="k-countdown-label">Regresando…</span>
</div>

<!-- PANEL DEV (pruebas / lector manual) -->
<div class="k-dev-panel" id="dev-panel">
  <p class="k-dev-title">🔌 Lector de huella</p>
  <div class="k-dev-row">
    <input type="text" id="dev-hash" class="k-dev-input" placeholder="Hash de huella…" autocomplete="off"
           onkeydown="if(event.key==='Enter') capturar()"/>
    <button class="k-dev-btn" onclick="capturar()">Validar</button>
  </div>
  <p class="k-dev-hint">Cuando el lector esté conectado llenará el campo automáticamente</p>
</div>

<script>
// ── Reloj ──────────────────────────────────────────
function actualizarReloj() {
  const now = new Date();
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  document.getElementById('k-clock').textContent = h + ':' + m;
}
actualizarReloj();
setInterval(actualizarReloj, 10000);

// ── Estado de la pantalla ──────────────────────────
let resetTimer   = null;
let cdInterval   = null;
let cdSeconds    = 5;
const CIRC       = 2 * Math.PI * 25; // 157

const kiosko     = document.getElementById('kiosko');
const kStatus    = document.getElementById('k-status');
const kNombre    = document.getElementById('k-nombre');
const kMembresia = document.getElementById('k-membresia');
const kDias      = document.getElementById('k-dias');
const kSub       = document.getElementById('k-sub');
const kCountdown = document.getElementById('k-countdown');
const cdCircle   = document.getElementById('cd-circle');
const cdNum      = document.getElementById('cd-num');
const iconWrap   = document.getElementById('k-icon-wrap');

const svgWaiting  = document.getElementById('svg-waiting');
const svgReading  = document.getElementById('svg-reading');
const svgPermitido= document.getElementById('svg-permitido');
const svgDenegado = document.getElementById('svg-denegado');

function showIcon(name) {
  svgWaiting.style.display   = name === 'waiting'  ? '' : 'none';
  svgReading.style.display   = name === 'reading'  ? '' : 'none';
  svgPermitido.style.display = name === 'permitido'? '' : 'none';
  svgDenegado.style.display  = name === 'denegado' ? '' : 'none';
}

function setState(state, data = {}) {
  kiosko.className = 'kiosko state-' + state;

  kNombre.style.display    = 'none';
  kMembresia.style.display = 'none';
  kDias.style.display      = 'none';

  if (state === 'waiting') {
    showIcon('waiting');
    kStatus.textContent = 'COLOCA TU DEDO EN EL LECTOR';
    kSub.textContent    = 'EgoGym Fitness · Control de Acceso';

  } else if (state === 'reading') {
    showIcon('reading');
    kStatus.textContent = 'LEYENDO HUELLA…';
    kSub.textContent    = '';

  } else if (state === 'permitido') {
    showIcon('permitido');
    iconWrap.classList.remove('pop');
    void iconWrap.offsetWidth;
    iconWrap.classList.add('pop');

    kStatus.textContent = 'ACCESO PERMITIDO';
    kSub.textContent    = '';

    if (data.cliente) {
      kNombre.textContent    = data.cliente.nombre.toUpperCase();
      kNombre.style.display  = '';
    }
    if (data.membresia) {
      kMembresia.textContent    = data.membresia.nombre;
      kMembresia.style.display  = '';
      kDias.textContent         = data.membresia.dias_restantes + ' días restantes · vence ' + data.membresia.fecha_fin;
      kDias.style.display       = '';
    }

  } else if (state === 'denegado') {
    showIcon('denegado');
    iconWrap.classList.remove('pop');
    void iconWrap.offsetWidth;
    iconWrap.classList.add('pop');

    kStatus.textContent = 'ACCESO DENEGADO';
    kSub.textContent    = '';

    if (data.cliente) {
      kNombre.textContent   = data.cliente.nombre.toUpperCase();
      kNombre.style.display = '';
    }
    kDias.textContent   = data.mensaje || 'Sin membresía activa';
    kDias.style.display = '';
  }
}

// ── Countdown ──────────────────────────────────────
function iniciarCountdown() {
  cdSeconds = 5;
  cdNum.textContent = cdSeconds;
  cdCircle.style.strokeDashoffset = 0;
  kCountdown.classList.add('visible');

  cdInterval = setInterval(() => {
    cdSeconds--;
    cdNum.textContent = cdSeconds;
    const progreso = ((5 - cdSeconds) / 5) * CIRC;
    cdCircle.style.strokeDashoffset = progreso;

    if (cdSeconds <= 0) {
      clearInterval(cdInterval);
      resetKiosko();
    }
  }, 1000);
}

function resetKiosko() {
  clearInterval(cdInterval);
  clearTimeout(resetTimer);
  kCountdown.classList.remove('visible');
  document.getElementById('dev-hash').value = '';
  setState('waiting');
}

// ── Captura y validación ───────────────────────────
function capturar() {
  const hash = document.getElementById('dev-hash').value.trim();
  if (!hash) return;

  clearInterval(cdInterval);
  clearTimeout(resetTimer);
  kCountdown.classList.remove('visible');

  setState('reading');

  fetch('ajax_validar_huella.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ huella_hash: hash })
  })
  .then(r => r.json())
  .then(data => {
    if (data.permitido) {
      setState('permitido', data);
    } else {
      setState('denegado', data);
    }
    iniciarCountdown();
  })
  .catch(() => {
    setState('denegado', { mensaje: 'Error de conexión' });
    iniciarCountdown();
  });
}

// ── API pública para el lector físico ─────────────
// Cuando el SDK del lector capture una huella, llamar:
//   window.recibirHuella('hash_aqui')
window.recibirHuella = function(hash) {
  document.getElementById('dev-hash').value = hash;
  capturar();
};
</script>
</body>
</html>
