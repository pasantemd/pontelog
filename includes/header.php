<?php $home = $page === 'inicio'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#000000">
  <title><?= e($title) ?> - Pontelog</title>
  <meta name="description" content="Soluciones de Pontelog para la seguridad alimentaria: microbiología, muestreo, higiene y detección.">
  <link rel="icon" href="<?= asset('cropped-cropped-Logo-PONTE-32x32.png') ?>">
  <link rel="stylesheet" href="<?= $base ?>assets/fonts.css">
  <link rel="stylesheet" href="<?= $base ?>assets/site.css?v=6">
  <?php if (!$home): ?><link rel="stylesheet" href="<?= $base ?>assets/forms.css?v=4"><?php endif; ?>
  <script src="<?= $base ?>assets/site.js?v=4" defer></script>
</head>
<body>
<a class="skip-link" href="#contenido">Saltar al contenido</a>
<header class="site-header" id="inicio">
  <div class="container header-inner">
    <a class="brand" href="<?= $base ?>" aria-label="Pontelog, inicio"><img src="<?= asset('cropped-Logo-PONTE.png') ?>" alt="Pontelog" width="175" height="48"></a>
    <button class="menu-toggle" aria-expanded="false" aria-controls="main-nav"><span>Menú</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
    <nav id="main-nav" aria-label="Navegación principal">
      <a href="<?= $base ?>#Soluciones">Soluciones</a><a href="<?= $base ?>#nosotros">Empresa</a><a href="<?= $base ?>#Contactenos">Contacto</a>
      <a class="button button-small nav-contact" href="<?= $base ?>#Contactenos">Consultar una solución</a>
    </nav>
  </div>
</header>
