<?php
declare(strict_types=1);
date_default_timezone_set('America/Guayaquil');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
require __DIR__ . '/includes/contact.php';
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
if ($base === '//') $base = '/';
function asset(string $file): string { global $base; return $base . 'assets/images/' . $file; }
$page = $_GET['page'] ?? 'inicio';
$allowed = ['inicio' => 'Soluciones para la seguridad alimentaria', 'invitacion-de-webinar-2026' => 'Webinar: patógenos bajo vigilancia', 'formulario-pontelog' => 'Formulario de campaña'];
if (!is_string($page) || !isset($allowed[$page])) { http_response_code(404); $page = '404'; }
$title = $allowed[$page] ?? 'Página no encontrada';
$site = require __DIR__ . '/content/site.php';
$contact = handle_contact($page === 'inicio' && $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : [], $_SESSION);
$token = csrf_token($_SESSION);
require __DIR__ . '/includes/header.php';
?>
<main id="contenido">
<?php if ($page === 'inicio'): ?>
<section class="hero" aria-labelledby="hero-title">
  <div class="container hero-layout">
    <div class="hero-copy">
      <h1 id="hero-title">La seguridad<br>alimentaria<br><span>empieza aquí.</span></h1>
      <p>Métodos para detectar la contaminación en agua, alimentos, cosméticos, materias primas y superficies.</p>
      <div class="hero-actions"><a class="button" href="#Soluciones">Ver soluciones <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a><a class="text-link" href="#Contactenos">Contáctenos</a></div>
    </div>
    <div class="hero-visual"><img class="hero-photo" src="<?= asset('soluciones-scaled.jpg') ?>" alt="Análisis de alimentos en un laboratorio con material de muestreo" width="1000" height="1200" fetchpriority="high"></div>
  </div>
</section>
<section id="Soluciones" class="solutions section-space" aria-labelledby="solutions-title">
  <div class="container">
    <div class="section-heading"><h2 id="solutions-title">Nuestras soluciones</h2></div>
    <div class="solution-grid" id="solution-grid">
    <?php foreach ($site['solutions'] as $solution): ?>
      <article class="solution-tile" id="marca-<?= e($solution['id']) ?>">
        <div class="flip-inner">
          <button class="flip-front" type="button" aria-expanded="false" aria-controls="info-<?= e($solution['id']) ?>" aria-label="Ver información de <?= e($solution['name']) ?>"><img src="<?= asset($solution['image']) ?>" alt="<?= e($solution['name']) ?>" loading="lazy" width="350" height="260"></button>
          <div class="flip-back" id="info-<?= e($solution['id']) ?>" inert>
            <p><?= e($solution['text']) ?></p>
            <a href="#Contactenos" class="button" data-brand="<?= e($solution['name']) ?>">Consultar</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="about section-space" id="nosotros" aria-labelledby="about-title">
  <div class="container about-layout">
    <div class="about-image"><img src="<?= asset('seguridad-alimentaria-scaled.jpg') ?>" alt="Profesional revisando la cadena de suministro en una bodega" width="900" height="1100" loading="lazy"></div>
    <div class="about-copy"><h2 id="about-title">Nuestra empresa</h2><p class="about-intro"><?= e($site['company'][0]['text']) ?>.</p><div class="company-principles"><?php foreach (array_slice($site['company'],1) as $item): ?><article><h3><?= e($item['title']) ?></h3><p><?= e($item['text']) ?></p></article><?php endforeach; ?></div></div>
  </div>
</section>
<section id="Contactenos" class="contact-section section-space" aria-labelledby="contact-title">
  <div class="container contact-layout">
    <div class="contact-copy"><h2 id="contact-title">Contáctenos</h2><p>Escríbenos para conocer nuestras soluciones.</p><a class="contact-email" href="mailto:info@pontelog.com">info@pontelog.com <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM4 5l8 7 8-7"/></svg></a>
      <div class="map-panel contact-map"><div id="coverage-map" aria-label="Mapa de países incluidos en el sitio original"></div><details class="country-details"><summary>Ver países en el mapa</summary><p><?= e(implode(', ', array_map(fn($m) => rtrim($m['name'], ':'), json_decode(file_get_contents(__DIR__ . '/assets/map-markers.json'), true)))) ?>.</p></details><p class="map-note">Escríbenos para confirmar la atención y disponibilidad en tu país.</p></div>
    </div>
    <?php require __DIR__ . '/includes/contact-form.php'; ?>
  </div>
</section>
<?php elseif ($page === '404'): ?>
<div class="container standalone not-found"><h1>Esta página no está disponible.</h1><p>Puedes volver al inicio para consultar nuestras soluciones.</p><a class="button" href="<?= $base ?>#Soluciones">Ver soluciones</a></div>
<?php else: require __DIR__ . '/includes/extra-page.php'; endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
