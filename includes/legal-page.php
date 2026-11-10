<?php
declare(strict_types=1);
/** @var array{title:string,updated:string,sections:list<array{heading:string,body:list<string>}>} $legal */
?>
<div class="container standalone legal-page">
  <h1><?= e($legal['title']) ?></h1>
  <p class="legal-updated">Última actualización: <?= e($legal['updated']) ?></p>
  <?php foreach ($legal['sections'] as $section): ?>
    <section class="legal-section">
      <h2><?= e($section['heading']) ?></h2>
      <?php foreach ($section['body'] as $paragraph): ?>
        <p><?= e($paragraph) ?></p>
      <?php endforeach; ?>
    </section>
  <?php endforeach; ?>
  <p><a class="button" href="<?= $base ?>">Volver al inicio</a></p>
</div>
