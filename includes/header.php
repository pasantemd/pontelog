<?php $home = $page === 'inicio'; ?>
<!doctype html>
<html lang="<?= e(pontelog_html_lang($lang)) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#000000">
  <title><?= e($title) ?> - Pontelog</title>
  <meta name="description" content="<?= e(t('meta.description')) ?>">
  <link rel="icon" href="<?= asset('cropped-cropped-Logo-PONTE-32x32.png') ?>">
  <link rel="stylesheet" href="<?= $base ?>assets/fonts.css">
  <link rel="stylesheet" href="<?= $base ?>assets/site.css?v=10">
  <?php if (!$home): ?><link rel="stylesheet" href="<?= $base ?>assets/forms.css?v=4"><?php endif; ?>
  <script src="<?= $base ?>assets/site.js?v=9" defer></script>
</head>
<body>
<a class="skip-link" href="#contenido"><?= e(t('skip')) ?></a>
<header class="site-header" id="inicio">
  <div class="container header-inner">
    <a class="brand" href="<?= $base ?>?lang=<?= e($lang) ?>" aria-label="<?= e(t('nav.home_aria')) ?>"><img src="<?= asset('cropped-Logo-PONTE.png') ?>" alt="Pontelog" width="175" height="48"></a>
    <button class="menu-toggle" aria-expanded="false" aria-controls="main-nav"><span><?= e(t('nav.menu')) ?></span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
    <nav id="main-nav" aria-label="<?= e(t('nav.aria')) ?>">
      <a href="<?= $base ?>?lang=<?= e($lang) ?>#Soluciones"><?= e(t('nav.solutions')) ?></a>
      <a href="<?= $base ?>?lang=<?= e($lang) ?>#nosotros"><?= e(t('nav.company')) ?></a>
      <a href="<?= $base ?>?lang=<?= e($lang) ?>#Contactenos"><?= e(t('nav.contact')) ?></a>
      <div class="lang-switch" aria-label="<?= e(t('lang.aria')) ?>">
        <?php foreach (PONTELOG_LANGS as $code): ?>
          <a href="<?= e(lang_url($code)) ?>" hreflang="<?= e(pontelog_html_lang($code)) ?>" lang="<?= e(pontelog_html_lang($code)) ?>"<?= $code === $lang ? ' aria-current="true" class="is-active"' : '' ?>><?= e(pontelog_lang_label($code)) ?></a>
        <?php endforeach; ?>
      </div>
      <a class="button button-small nav-contact" href="<?= $base ?>?lang=<?= e($lang) ?>#Contactenos"><?= e(t('nav.cta')) ?></a>
    </nav>
  </div>
</header>
