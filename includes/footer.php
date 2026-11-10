<footer class="site-footer">
  <div class="container footer-main">
    <div class="footer-intro"><a class="footer-logo" href="<?= $base ?>?lang=<?= e($lang) ?>" aria-label="<?= e(t('nav.home_aria')) ?>"><img src="<?= asset('cropped-Logo-PONTE.png') ?>" alt="Pontelog" width="175" height="48" loading="lazy"></a><p><?= t('footer.tagline') ?></p><a class="footer-email" href="mailto:info@pontelog.com">info@pontelog.com</a></div>
    <nav class="footer-nav" aria-label="<?= e(t('nav.aria')) ?>"><a href="<?= $base ?>?lang=<?= e($lang) ?>#Soluciones"><?= e(t('footer.solutions')) ?></a><a href="<?= $base ?>?lang=<?= e($lang) ?>#nosotros"><?= e(t('footer.company')) ?></a><a href="<?= $base ?>?lang=<?= e($lang) ?>#Contactenos"><?= e(t('footer.contact')) ?></a><a href="<?= $base ?>politica-de-privacidad/?lang=<?= e($lang) ?>"><?= e(t('footer.privacy')) ?></a><a href="<?= $base ?>politica-de-cookies/?lang=<?= e($lang) ?>"><?= e(t('footer.cookies')) ?></a></nav>
  </div>
  <div class="container footer-bottom"><p><?= e(t('footer.rights', ['year' => date('Y')])) ?></p></div>
</footer>
<?php require __DIR__ . '/cookie-banner.php'; ?>
</body>
</html>
