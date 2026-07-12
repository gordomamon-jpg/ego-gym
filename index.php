<?php
// ── Session y estado de autenticación ──────────────
if (session_status() === PHP_SESSION_NONE) session_start();
$logged_in      = !empty($_SESSION['cliente_id']);
$cliente_nombre = $_SESSION['cliente_nombre'] ?? '';
$primera_letra  = $cliente_nombre ? strtoupper(substr($cliente_nombre, 0, 1)) : '';
$s              = 'sistema/cliente'; // ruta base del panel cliente
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="description" content="EgoGym Fitness — Gimnasio en Miguel Hidalgo, CDMX. Sin costo de inscripción. Membresías desde $60. Abierto todos los días incluyendo festivos."/>
<meta name="keywords" content="gimnasio Miguel Hidalgo, gym CDMX, EgoGym Fitness, gym barato CDMX, gimnasio sin inscripción"/>
<title>EgoGym Fitness — Tu tiempo es hoy</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="css/styles.css"/>
<style>
/* ── Auth navbar ── */
.nav-auth { display:flex; align-items:center; gap:8px; }
.nav-user  { display:flex; align-items:center; gap:10px; }
.nav-avatar {
  width:32px; height:32px; border-radius:50%;
  background:linear-gradient(135deg,var(--tq),var(--pu));
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fh); font-weight:900; font-size:.85rem; color:var(--bg);
  flex-shrink:0;
}
.nav-user-name { font-size:.78rem; font-weight:500; color:var(--light); max-width:100px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.btn-auth { padding:9px 18px; font-size:.78rem; }
.btn-register { background:linear-gradient(90deg,var(--tq),var(--pu)); color:var(--bg); }
.btn-register:hover { filter:drop-shadow(0 4px 14px rgba(56,224,208,.35)); }
.btn-login { background:rgba(255,255,255,.06); color:var(--white); border:1px solid var(--border); }
.btn-login:hover { background:rgba(255,255,255,.1); }
.btn-cuenta { background:var(--tq-lo); color:var(--tq); border:1px solid var(--tq-border); }
.btn-cuenta:hover { background:rgba(56,224,208,.22); }
</style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<header id="nav" role="banner">
  <div class="ni">
    <a href="#hero" class="nl" aria-label="EgoGym Fitness"><em>EGOGYM</em> FITNESS</a>
    <nav aria-label="Principal">
      <ul class="nlinks">
        <li><a href="#about">Nosotros</a></li>
        <li><a href="#galeria">Galería</a></li>
        <li><a href="#precios">Precios</a></li>
        <li><a href="#horarios">Horarios</a></li>
        <li><a href="#ubicacion">Ubicación</a></li>
        <li><a href="#faq">FAQ</a></li>
      </ul>
    </nav>

    <?php if ($logged_in): ?>
      <!-- Usuario logueado -->
      <div class="nav-auth">
        <a href="<?= $s ?>/dashboard.php" class="btn btn-auth btn-cuenta" aria-label="Mi cuenta">
          <span>Mi cuenta</span>
        </a>
        <div class="nav-user">
          <div class="nav-avatar" title="<?= htmlspecialchars($cliente_nombre) ?>"><?= $primera_letra ?></div>
        </div>
      </div>
    <?php else: ?>
      <!-- Visitante -->
      <div class="nav-auth">
        <a href="<?= $s ?>/login.php"    class="btn btn-auth btn-login"    aria-label="Iniciar sesión">Iniciar sesión</a>
        <a href="<?= $s ?>/registro.php" class="btn btn-auth btn-register" aria-label="Registrarse gratis">Registrarse</a>
      </div>
    <?php endif; ?>

    <button class="hbg" onclick="tNav()" aria-label="Menú" aria-expanded="false" aria-controls="mmenu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <!-- Menú móvil -->
  <div id="mmenu" class="mm" role="navigation" aria-label="Menú móvil">
    <a href="#about"     onclick="cNav()">Nosotros</a>
    <a href="#galeria"   onclick="cNav()">Galería</a>
    <a href="#precios"   onclick="cNav()">Precios</a>
    <a href="#horarios"  onclick="cNav()">Horarios</a>
    <a href="#ubicacion" onclick="cNav()">Ubicación</a>
    <a href="#faq"       onclick="cNav()">FAQ</a>
    <a href="https://www.instagram.com/egogym.fitness30/" target="_blank" rel="noopener" onclick="cNav()" style="color:var(--tq)">Instagram →</a>
    <?php if ($logged_in): ?>
      <a href="<?= $s ?>/dashboard.php" onclick="cNav()" style="color:var(--tq);font-weight:600">Mi cuenta →</a>
      <a href="<?= $s ?>/logout.php"    onclick="cNav()" style="color:var(--muted)">Cerrar sesión</a>
    <?php else: ?>
      <a href="<?= $s ?>/login.php"    onclick="cNav()" style="color:var(--tq);font-weight:600">Iniciar sesión →</a>
      <a href="<?= $s ?>/registro.php" onclick="cNav()" style="color:var(--tq);font-weight:600">Registrarse gratis →</a>
    <?php endif; ?>
  </div>
</header>

<!-- ══ HERO ══ -->
<section id="hero" aria-label="EgoGym Fitness bienvenida">
  <div class="hbg-base" aria-hidden="true"></div>
  <div class="hbg-grad" aria-hidden="true"></div>
  <div class="hgrid"    aria-hidden="true"></div>
  <div class="orb o1"   aria-hidden="true"></div>
  <div class="orb o2"   aria-hidden="true"></div>
  <div class="orb o3"   aria-hidden="true"></div>

  <div class="hc">
    <p class="heyebrow">Gimnasio en Miguel Hidalgo · CDMX</p>
    <h1 class="htitle">
      <span class="ht1">EGOGYM</span>
      <span class="ht2">FITNESS</span>
    </h1>
    <p class="hslogan">Tu tiempo es hoy</p>
    <div class="hbadge" aria-label="Sin costo de inscripción">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
      Sin costo de inscripción
    </div>
    <div class="hctas">
      <a href="#precios" class="btn btn-p" aria-label="Ver precios">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        <span>Ver precios</span>
      </a>
      <?php if ($logged_in): ?>
        <a href="<?= $s ?>/membresias.php" class="btn btn-o" aria-label="Ver mis membresías">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
          Mis membresías
        </a>
      <?php else: ?>
        <a href="<?= $s ?>/registro.php" class="btn btn-o" aria-label="Registrarse gratis">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          Registrarse gratis
        </a>
      <?php endif; ?>
      <a href="#ubicacion" class="btn btn-g" aria-label="Ver ubicación">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        Ver ubicación
      </a>
    </div>
    <div class="hstats" role="list" aria-label="Datos clave EgoGym Fitness">
      <div class="hstat" role="listitem"><p class="hstat-n">$0</p><p class="hstat-l">Inscripción</p></div>
      <div class="hstat" role="listitem"><p class="hstat-n">7/7</p><p class="hstat-l">Días abierto</p></div>
      <div class="hstat" role="listitem"><p class="hstat-n">$60</p><p class="hstat-l">Desde</p></div>
      <div class="hstat" role="listitem"><p class="hstat-n">100%</p><p class="hstat-l">Compromiso</p></div>
    </div>
  </div>
  <div class="hscroll" aria-hidden="true"><div class="harrow"></div></div>
</section>

<!-- ══ NOSOTROS + SERVICIOS ══ -->
<section id="about" class="pad" aria-label="Sobre EgoGym Fitness y servicios">
  <div class="wrap">
    <div class="about-grid">

      <div class="rv">
        <p class="eyebrow">Bienvenido</p>
        <h2 class="h2">Sé parte de la<br/><span class="gt">familia EGO</span></h2>
        <div class="bar"></div>
        <div class="no-fee" aria-label="Sin costo de inscripción">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
          Sin costo de inscripción
        </div>
        <p class="sub" style="margin-bottom:20px">
          EgoGym Fitness es un espacio diseñado para personas que entrenan con propósito. Queremos que seas parte de esta familia: un lugar donde la disciplina, los resultados y la comunidad van de la mano.
        </p>
        <p class="sub">
          Contamos con equipo moderno, instructores certificados y el mejor ambiente para que no faltes ni un día. Abrimos <strong style="color:var(--white)">todos los días</strong>, incluyendo días festivos.
        </p>
        <div style="margin-top:28px;display:flex;gap:10px;flex-wrap:wrap">
          <a href="#precios" class="btn btn-p"><span>Ver planes</span></a>
          <a href="#ubicacion" class="btn btn-g">Cómo llegar</a>
        </div>
      </div>

      <div>
        <p class="eyebrow rv d1">Lo que incluye</p>
        <h3 class="h2 rv d1" style="font-size:clamp(1.5rem,3vw,2.2rem);margin-bottom:20px">Nuestros <span class="gt">servicios</span></h3>
        <div class="svc-grid rv d2">
          <div class="svc" aria-label="Área de pesas">
            <div class="svc-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6.5 6.5h11M6.5 17.5h11M2 9.5h20M2 14.5h20"/></svg>
            </div>
            <span class="svc-name">Área de pesas</span>
          </div>
          <div class="svc" aria-label="Zona cardiovascular">
            <div class="svc-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7A3CFF" stroke-width="2" stroke-linecap="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <span class="svc-name">Cardiovascular</span>
          </div>
          <div class="svc" aria-label="Instructores certificados">
            <div class="svc-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
            </div>
            <span class="svc-name">Instructores certificados</span>
          </div>
          <div class="svc" aria-label="WiFi gratis">
            <div class="svc-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7A3CFF" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><circle cx="12" cy="20" r="1" fill="#7A3CFF"/></svg>
            </div>
            <span class="svc-name">WiFi</span>
          </div>
          <div class="svc svc-span" aria-label="Sanitarios">
            <div class="svc-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 6 C9 3.79 10.34 2 12 2 C13.66 2 15 3.79 15 6"/><path d="M4 20 C4 15 8 12 12 12 C16 12 20 15 20 20"/><line x1="12" y1="12" x2="12" y2="6"/></svg>
            </div>
            <span class="svc-name">Sanitarios (WC)</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══ GALERÍA ══ -->
<section id="galeria" class="pad" aria-label="Galería y ambiente EgoGym Fitness">
  <div class="wrap">
    <div class="rv" style="text-align:center;margin-bottom:48px">
      <p class="eyebrow">Nuestro espacio</p>
      <h2 class="h2">Donde ocurre la <span class="gt">transformación</span></h2>
      <div class="bar" style="margin:16px auto 0"></div>
    </div>
    <div class="gal-grid rv d1">
      <div class="gal-item">
        <div class="gal-inner">
          <div class="gal-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6.5 6.5h11M6.5 17.5h11M2 9.5h20M2 14.5h20"/></svg></div>
          <p class="gal-lbl">Área de Pesas</p>
        </div>
        <div class="gal-bar"></div>
      </div>
      <div class="gal-item">
        <div class="gal-inner">
          <div class="gal-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7A3CFF" stroke-width="2" stroke-linecap="round" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
          <p class="gal-lbl">Zona Cardio</p>
        </div>
        <div class="gal-bar"></div>
      </div>
      <div class="gal-item">
        <div class="gal-inner">
          <div class="gal-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg></div>
          <p class="gal-lbl">Instructores</p>
        </div>
        <div class="gal-bar"></div>
      </div>
      <div class="gal-item">
        <div class="gal-inner">
          <div class="gal-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7A3CFF" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
          <p class="gal-lbl">Comunidad</p>
        </div>
        <div class="gal-bar"></div>
      </div>
      <div class="gal-item">
        <div class="gal-inner">
          <div class="gal-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
          <p class="gal-lbl">Horarios Flex</p>
        </div>
        <div class="gal-bar"></div>
      </div>
      <div class="gal-item">
        <div class="gal-inner" style="gap:10px">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="rgba(56,224,208,.5)" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          <p class="gal-lbl">Síguenos<br/>@egogym.fitness30</p>
          <a href="https://www.instagram.com/egogym.fitness30/" target="_blank" rel="noopener" class="btn btn-ig" style="font-size:.66rem;padding:8px 16px;letter-spacing:1px" aria-label="Ver galería en Instagram">Ver galería →</a>
        </div>
        <div class="gal-bar"></div>
      </div>
    </div>
  </div>
</section>

<!-- ══ PRECIOS ══ -->
<section id="precios" class="pad" aria-label="Precios y membresías de EgoGym Fitness">
  <div class="wrap">
    <div class="rv" style="text-align:center;margin-bottom:48px">
      <p class="eyebrow">Transparencia total</p>
      <h2 class="h2">Nuestros <span class="gt">precios</span></h2>
      <div class="bar" style="margin:16px auto 16px"></div>
      <p class="sub" style="margin:0 auto;max-width:480px">Sin costo de inscripción. Elige el plan que va con tu ritmo y comienza hoy.</p>
    </div>
    <div class="prices-grid">

      <article class="pc rv d1" aria-label="Plan 1 día">
        <p class="pc-tag">Prueba el gym</p>
        <h3 class="pc-name">1 Día</h3>
        <p class="pc-price"><span class="pc-peso">$</span>60</p>
        <p class="pc-note">Pase por un día. Acceso completo a todas las zonas de la instalación.</p>
        <a href="<?= $s ?>/membresias.php" class="btn btn-g pc-cta" aria-label="Contratar plan 1 día">
          <?= $logged_in ? 'Contratar' : 'Iniciar sesión' ?>
        </a>
      </article>

      <article class="pc rv d2" aria-label="Plan mensual">
        <p class="pc-tag">Comienza el hábito</p>
        <h3 class="pc-name">Mensual</h3>
        <p class="pc-price"><span class="pc-peso">$</span>550</p>
        <p class="pc-note">30 días de acceso ilimitado. Sin costo de inscripción.</p>
        <a href="<?= $s ?>/membresias.php" class="btn btn-g pc-cta" aria-label="Contratar plan mensual">
          <?= $logged_in ? 'Contratar' : 'Iniciar sesión' ?>
        </a>
      </article>

      <article class="pc hot rv d3" aria-label="Plan trimestral — más popular">
        <p class="pc-tag">— Más popular —</p>
        <h3 class="pc-name">Trimestral</h3>
        <p class="pc-price"><span class="pc-peso">$</span>1,350</p>
        <p class="pc-note">90 días ilimitados. Ahorra con 3 meses y obtén asesoría nutricional básica.</p>
        <a href="<?= $s ?>/membresias.php" class="btn btn-p pc-cta" aria-label="Contratar plan trimestral">
          <span><?= $logged_in ? 'Contratar' : 'Inscribirme' ?></span>
        </a>
      </article>

      <article class="pc rv d4" aria-label="Plan semestral">
        <p class="pc-tag">Mayor ahorro</p>
        <h3 class="pc-name">Semestral</h3>
        <p class="pc-price"><span class="pc-peso">$</span>2,400</p>
        <p class="pc-note">180 días. Evaluación mensual, asesoría nutricional y acceso a todas las clases.</p>
        <a href="<?= $s ?>/membresias.php" class="btn btn-g pc-cta" aria-label="Contratar plan semestral">
          <?= $logged_in ? 'Contratar' : 'Iniciar sesión' ?>
        </a>
      </article>

      <article class="pc rv d5" aria-label="Plan anual">
        <p class="pc-tag">El mejor precio/día</p>
        <h3 class="pc-name">Anual</h3>
        <p class="pc-price"><span class="pc-peso">$</span>4,200</p>
        <p class="pc-note">365 días + plan personalizado + congelamiento de membresía incluido.</p>
        <a href="<?= $s ?>/membresias.php" class="btn btn-g pc-cta" aria-label="Contratar plan anual">
          <?= $logged_in ? 'Contratar' : 'Iniciar sesión' ?>
        </a>
      </article>

    </div>

    <!-- CTA de registro -->
    <?php if (!$logged_in): ?>
    <div class="rv" style="text-align:center;margin-top:36px">
      <p style="font-size:.88rem;color:var(--muted);margin-bottom:16px">¿Listo para empezar? Crea tu cuenta gratis y elige tu plan.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= $s ?>/registro.php" class="btn btn-p" aria-label="Crear cuenta gratis">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          <span>Crear cuenta gratis</span>
        </a>
        <a href="<?= $s ?>/login.php" class="btn btn-g" aria-label="Ya tengo cuenta">Ya tengo cuenta</a>
      </div>
    </div>
    <?php else: ?>
    <div class="rv" style="text-align:center;margin-top:28px">
      <a href="<?= $s ?>/membresias.php" class="btn btn-p" aria-label="Ver todos los planes">
        <span>Ver todos mis planes disponibles</span>
      </a>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ══ HORARIOS ══ -->
<section id="horarios" class="pad" aria-label="Horarios de EgoGym Fitness">
  <div class="wrap">
    <div class="rv" style="text-align:center;margin-bottom:48px">
      <p class="eyebrow">Siempre abiertos</p>
      <h2 class="h2">Nuestros <span class="gt">horarios</span></h2>
      <div class="bar" style="margin:16px auto 0"></div>
    </div>
    <div class="sched-wrap">
      <article class="sc hl rv d1" aria-label="Horario de lunes a viernes">
        <div class="sc-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7A3CFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <p class="sc-days">Lunes — Viernes</p>
        <p class="sc-time">6:00 AM — 10:00 PM</p>
        <p class="sc-sub">16 horas disponibles · Lunes a viernes</p>
      </article>
      <article class="sc rv d2" aria-label="Horario de sábado y domingo">
        <div class="sc-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <p class="sc-days">Sábado y Domingo</p>
        <p class="sc-time">7:00 AM — 5:00 PM</p>
        <p class="sc-sub">10 horas disponibles · Fin de semana</p>
        <div class="festivo" aria-label="Incluye días festivos">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
          Incluye días festivos
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ══ TESTIMONIOS ══ -->
<section id="testimonios" class="pad" aria-label="Testimonios de miembros">
  <div class="wrap">
    <div class="rv" style="text-align:center;margin-bottom:48px">
      <p class="eyebrow">Lo que dicen</p>
      <h2 class="h2">Nuestra <span class="gt">comunidad</span></h2>
      <div class="bar" style="margin:16px auto 20px"></div>
      <div style="display:inline-flex;align-items:center;gap:10px;padding:10px 22px;border:1px solid var(--tq-border);border-radius:30px;background:rgba(56,224,208,.05)">
        <div style="display:flex;gap:3px" aria-label="Calificación 5 de 5 estrellas">
          <?php for($i=0;$i<5;$i++): ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="#38E0D0" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <?php endfor; ?>
        </div>
        <span style="font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:1.25rem;color:var(--white)">4.9</span>
        <span style="font-size:.74rem;color:var(--muted)">Miembros satisfechos</span>
      </div>
    </div>
    <div class="tgrid">
      <article class="tc rv d1" aria-label="Testimonio de Rodrigo M.">
        <div class="tstars" aria-label="5 estrellas">
          <?php for($i=0;$i<5;$i++): ?><svg class="ts" viewBox="0 0 24 24" fill="#38E0D0" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><?php endfor; ?>
        </div>
        <p class="ttext">"Excelente gym. El ambiente es muy bueno y la gente es respetuosa. Me gusta que abre temprano para ir antes del trabajo, y los instructores siempre están al pendiente."</p>
        <div class="tauth">
          <div class="tav" aria-hidden="true">R</div>
          <div><p class="tname">Rodrigo M.</p><p class="ttag">Miembro activo</p></div>
        </div>
      </article>
      <article class="tc rv d2" aria-label="Testimonio de Valeria S.">
        <div class="tstars" aria-label="5 estrellas">
          <?php for($i=0;$i<5;$i++): ?><svg class="ts" viewBox="0 0 24 24" fill="#38E0D0" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><?php endfor; ?>
        </div>
        <p class="ttext">"Instalaciones modernas y muy limpias. El plan mensual es muy accesible para todo lo que ofrecen. Me encanta que no tienen costo de inscripción. Totalmente recomendado."</p>
        <div class="tauth">
          <div class="tav" aria-hidden="true">V</div>
          <div><p class="tname">Valeria S.</p><p class="ttag">Plan mensual</p></div>
        </div>
      </article>
      <article class="tc rv d3" aria-label="Testimonio de Carlos y Ana">
        <div class="tstars" aria-label="4 estrellas">
          <?php for($i=0;$i<4;$i++): ?><svg class="ts" viewBox="0 0 24 24" fill="#38E0D0" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg><?php endfor; ?>
          <svg class="ts" viewBox="0 0 24 24" fill="#7A3CFF" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <p class="ttext">"Mi pareja y yo venimos juntos. El gym abre hasta en días festivos, eso marca la diferencia con otros lugares. El ambiente es inmejorable y el equipo está en muy buen estado."</p>
        <div class="tauth">
          <div class="tav" aria-hidden="true">C</div>
          <div><p class="tname">Carlos &amp; Ana</p><p class="ttag">Miembros activos</p></div>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ══ UBICACIÓN + CONTACTO ══ -->
<section id="ubicacion" class="pad" aria-label="Ubicación y contacto de EgoGym Fitness">
  <div class="wrap">
    <div class="uloc-grid">
      <div class="mwrap rv" aria-label="Mapa de EgoGym Fitness">
        <iframe
          src="https://maps.google.com/maps?q=Lago+Mayor+30,+Los+Manzanos,+Miguel+Hidalgo,+11460+Ciudad+de+Mexico,+CDMX&output=embed&z=16"
          loading="lazy"
          title="Mapa EgoGym Fitness, Miguel Hidalgo, CDMX"
          allowfullscreen
          referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
      </div>
      <div class="rv d2">
        <p class="eyebrow">Visítanos</p>
        <h2 class="h2">Encuéntranos<br/>en <span class="gt">CDMX</span></h2>
        <div class="bar"></div>
        <div class="uinfo">
          <div class="ui">
            <div class="uico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
            <div>
              <p class="ulbl">Dirección</p>
              <p class="uval">Lago Mayor 30, Los Manzanos<br/>Miguel Hidalgo, 11460 CDMX</p>
            </div>
          </div>
          <div class="ui">
            <div class="uico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7A3CFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div>
              <p class="ulbl">Hoy estamos abiertos</p>
              <p class="uval" id="today-h">Cargando…</p>
            </div>
          </div>
          <div class="ui">
            <div class="uico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></div>
            <div>
              <p class="ulbl">Instagram</p>
              <p class="uval"><a href="https://www.instagram.com/egogym.fitness30/" target="_blank" rel="noopener">@egogym.fitness30</a></p>
            </div>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:6px">
            <a href="https://maps.google.com/?q=Lago+Mayor+30,+Los+Manzanos,+Miguel+Hidalgo,+11460+Ciudad+de+Mexico,+CDMX" target="_blank" rel="noopener" class="btn btn-p" aria-label="Cómo llegar">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span>Cómo llegar</span>
            </a>
            <a href="https://www.instagram.com/egogym.fitness30/" target="_blank" rel="noopener" class="btn btn-ig" aria-label="Escríbenos">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
              Escríbenos
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ FAQ ══ -->
<section id="faq" class="pad" aria-label="Preguntas frecuentes EgoGym Fitness">
  <div class="wrap">
    <div class="rv" style="text-align:center;margin-bottom:48px">
      <p class="eyebrow">Resolvemos tus dudas</p>
      <h2 class="h2">Preguntas <span class="gt">frecuentes</span></h2>
      <div class="bar" style="margin:16px auto 0"></div>
    </div>
    <div class="faq-inner rv d1">
      <div class="fi">
        <button class="fq" onclick="tFaq(this)" aria-expanded="false">
          <span class="fqt">¿Tienen costo de inscripción?</span>
          <div class="fico" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
        </button>
        <div class="fa">No. En EgoGym Fitness no cobramos costo de inscripción. Solo pagas tu plan y listo, puedes comenzar de inmediato.</div>
      </div>
      <div class="fi">
        <button class="fq" onclick="tFaq(this)" aria-expanded="false">
          <span class="fqt">¿Cómo compro mi membresía en línea?</span>
          <div class="fico" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
        </button>
        <div class="fa">Crea tu cuenta gratis, elige tu plan y paga con tarjeta de forma segura. Recibirás un correo de confirmación con los detalles de tu membresía al instante.</div>
      </div>
      <div class="fi">
        <button class="fq" onclick="tFaq(this)" aria-expanded="false">
          <span class="fqt">¿Cuáles son los precios?</span>
          <div class="fico" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
        </button>
        <div class="fa">1 Día: $60 · Mensual: $550 · Trimestral: $1,350 · Semestral: $2,400 · Anual: $4,200. Sin costo de inscripción en todos los planes.</div>
      </div>
      <div class="fi">
        <button class="fq" onclick="tFaq(this)" aria-expanded="false">
          <span class="fqt">¿Cuáles son sus horarios?</span>
          <div class="fico" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
        </button>
        <div class="fa">Lunes a viernes de 6:00 AM a 10:00 PM. Sábado y domingo de 7:00 AM a 5:00 PM. Abrimos todos los días incluyendo días festivos.</div>
      </div>
      <div class="fi">
        <button class="fq" onclick="tFaq(this)" aria-expanded="false">
          <span class="fqt">¿Puedo asistir si soy principiante?</span>
          <div class="fico" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
        </button>
        <div class="fa">¡Claro que sí! Contamos con instructores certificados listos para orientarte. EgoGym Fitness es para todos los niveles: desde principiantes hasta atletas avanzados.</div>
      </div>
      <div class="fi">
        <button class="fq" onclick="tFaq(this)" aria-expanded="false">
          <span class="fqt">¿Cómo puedo contactarlos?</span>
          <div class="fico" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#38E0D0" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
        </button>
        <div class="fa">Puedes escribirnos directamente por Instagram: @egogym.fitness30 o visitarnos en Lago Mayor 30, Los Manzanos, Miguel Hidalgo, CDMX durante nuestros horarios de atención.</div>
      </div>
    </div>
  </div>
</section>

<!-- ══ FOOTER ══ -->
<footer role="contentinfo">
  <div class="wrap">
    <div class="fgrid">
      <div>
        <p class="flogo"><em>EGOGYM</em> FITNESS</p>
        <p class="fdesc">Tu tiempo es hoy. Gimnasio en Miguel Hidalgo, CDMX. Abierto todos los días incluyendo festivos.</p>
        <?php if (!$logged_in): ?>
        <div style="display:flex;gap:8px;margin-top:16px;flex-wrap:wrap">
          <a href="<?= $s ?>/registro.php" class="btn btn-p" style="padding:9px 18px;font-size:.78rem"><span>Crear cuenta</span></a>
          <a href="<?= $s ?>/login.php"    class="btn btn-g" style="padding:9px 18px;font-size:.78rem">Iniciar sesión</a>
        </div>
        <?php else: ?>
        <a href="<?= $s ?>/dashboard.php" class="btn btn-cuenta btn-auth" style="margin-top:14px;display:inline-flex">Mi cuenta →</a>
        <?php endif; ?>
      </div>
      <div class="fcol">
        <p class="fcol-t">Horarios</p>
        <ul>
          <li><a href="#horarios">Lun–Vie: 6:00 AM – 10:00 PM</a></li>
          <li><a href="#horarios">Sáb–Dom: 7:00 AM – 5:00 PM</a></li>
          <li><a href="#horarios">Festivos: abierto</a></li>
        </ul>
      </div>
      <div class="fcol">
        <p class="fcol-t">Planes</p>
        <ul>
          <li><a href="<?= $s ?>/membresias.php">1 Día — $60</a></li>
          <li><a href="<?= $s ?>/membresias.php">Mensual — $550</a></li>
          <li><a href="<?= $s ?>/membresias.php">Trimestral — $1,350</a></li>
          <li><a href="<?= $s ?>/membresias.php">Semestral — $2,400</a></li>
          <li><a href="<?= $s ?>/membresias.php">Anual — $4,200</a></li>
        </ul>
      </div>
    </div>
    <div class="fbot">
      <p class="fcopy">&copy; 2026 EgoGym Fitness · Gimnasio en Miguel Hidalgo, CDMX · Todos los derechos reservados</p>
      <a href="https://www.instagram.com/egogym.fitness30/" target="_blank" rel="noopener" class="fig" aria-label="Instagram EgoGym Fitness">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
        @egogym.fitness30
      </a>
    </div>
  </div>
</footer>

<!-- ══ FAB ══ -->
<?php if ($logged_in): ?>
<a href="<?= $s ?>/dashboard.php" class="fab" style="background:linear-gradient(90deg,var(--tq),var(--pu));box-shadow:0 8px 32px rgba(56,224,208,.4),0 2px 8px rgba(0,0,0,.35)" aria-label="Ir a mi cuenta">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
  <span class="fab-text">Mi cuenta</span>
</a>
<?php else: ?>
<a href="<?= $s ?>/registro.php" class="fab" aria-label="Regístrate en EgoGym Fitness">
  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
  <span class="fab-text">Regístrate</span>
</a>
<?php endif; ?>

<script src="js/main.js"></script>
</body>
</html>
