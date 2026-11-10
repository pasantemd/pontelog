<?php
declare(strict_types=1);
date_default_timezone_set('America/Guayaquil');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
require __DIR__ . '/includes/i18n.php';
$lang = pontelog_detect_lang();
require __DIR__ . '/includes/contact.php';
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
if ($base === '//') $base = '/';
function asset(string $file): string { global $base; return $base . 'assets/images/' . $file; }
$page = $_GET['page'] ?? 'inicio';
$allowedPages = ['inicio', 'invitacion-de-webinar-2026', 'formulario-pontelog', 'politica-de-privacidad', 'politica-de-cookies'];
if (!is_string($page) || !in_array($page, $allowedPages, true)) { http_response_code(404); $page = '404'; }
$title = t('title.' . $page);
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
      <h1 id="hero-title"><?= e(t('hero.line1')) ?><br><?= e(t('hero.line2')) ?><br><span><?= e(t('hero.line3')) ?></span></h1>
      <p><?= e(t('hero.text')) ?></p>
      <div class="hero-actions"><a class="button" href="#Soluciones"><?= e(t('hero.cta_solutions')) ?> <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a><a class="text-link" href="#Contactenos"><?= e(t('hero.cta_contact')) ?></a></div>
    </div>
    <div class="hero-visual"><img class="hero-photo" src="<?= asset('soluciones-scaled.jpg') ?>" alt="<?= e(t('hero.image_alt')) ?>" width="1000" height="1200" fetchpriority="high"></div>
  </div>
</section>
<section id="Soluciones" class="solutions section-space" aria-labelledby="solutions-title">
  <div class="container">
    <div class="section-heading"><h2 id="solutions-title"><?= e(t('solutions.title')) ?></h2></div>
    <div class="solution-grid" id="solution-grid">
    <?php foreach ($site['solutions'] as $solution): ?>
      <article class="solution-tile" id="marca-<?= e($solution['id']) ?>">
        <div class="flip-inner">
          <button class="flip-front" type="button" aria-expanded="false" aria-controls="info-<?= e($solution['id']) ?>" aria-label="<?= e(t('solutions.info_aria', ['name' => $solution['name']])) ?>"><img src="<?= asset($solution['image']) ?>" alt="<?= e($solution['name']) ?>" loading="lazy" width="350" height="260"></button>
          <div class="flip-back" id="info-<?= e($solution['id']) ?>" inert>
            <p><?= e(t('solution.' . $solution['id'])) ?></p>
            <a href="#Contactenos" class="button" data-brand="<?= e($solution['name']) ?>"><?= e(t('solutions.consult')) ?></a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="about section-space" id="nosotros" aria-labelledby="about-title">
  <div class="container about-layout">
    <div class="about-image"><img src="<?= asset('seguridad-alimentaria-scaled.jpg') ?>" alt="<?= e(t('about.image_alt')) ?>" width="900" height="1100" loading="lazy"></div>
    <div class="about-copy"><h2 id="about-title"><?= e(t('about.title')) ?></h2><p class="about-intro"><?= e(t('company.intro')) ?>.</p><div class="company-principles"><article><h3><?= e(t('company.mission_title')) ?></h3><p><?= e(t('company.mission_text')) ?></p></article><article><h3><?= e(t('company.vision_title')) ?></h3><p><?= e(t('company.vision_text')) ?></p></article></div></div>
  </div>
</section>
<section id="Contactenos" class="contact-section section-space" aria-labelledby="contact-title">
  <div class="container contact-layout">
    <div class="contact-copy"><h2 id="contact-title"><?= e(t('contact.title')) ?></h2><p><?= e(t('contact.intro')) ?></p><a class="contact-email" href="mailto:info@pontelog.com">info@pontelog.com <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM4 5l8 7 8-7"/></svg></a>
      <div class="map-panel contact-map">
        <div id="coverage-map" aria-label="<?= e(t('contact.map_aria')) ?>"></div>
        <p class="map-countries visually-hidden"><?= e(implode(', ', array_map(static fn($m) => rtrim((string) $m['name'], ':'), json_decode(file_get_contents(__DIR__ . '/assets/map-markers.json'), true) ?: []))) ?>.</p>
        <p class="map-note"><?= e(t('contact.map_note')) ?></p>
      </div>
    </div>
    <?php require __DIR__ . '/includes/contact-form.php'; ?>
  </div>
</section>
<?php elseif ($page === 'politica-de-privacidad'):
  $legalFile = __DIR__ . '/content/privacy-' . $lang . '.php';
  $legal = require is_file($legalFile) ? $legalFile : __DIR__ . '/content/privacy-es.php';
  require __DIR__ . '/includes/legal-page.php';
elseif ($page === 'politica-de-cookies'):
  $legalFile = __DIR__ . '/content/cookies-' . $lang . '.php';
  $legal = require is_file($legalFile) ? $legalFile : __DIR__ . '/content/cookies-es.php';
  require __DIR__ . '/includes/legal-page.php';
elseif ($page === '404'): ?>
<div class="container standalone not-found"><h1><?= e(t('notfound.title')) ?></h1><p><?= e(t('notfound.text')) ?></p><a class="button" href="<?= $base ?>?lang=<?= e($lang) ?>#Soluciones"><?= e(t('notfound.cta')) ?></a></div>
<?php else: require __DIR__ . '/includes/extra-page.php'; endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
