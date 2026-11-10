<?php
$cookieParts = explode(':privacy', t('cookie.text'), 2);
$afterPrivacy = $cookieParts[1] ?? '';
$cookieTail = explode(':cookies', $afterPrivacy, 2);
?>
<div id="cookie-banner" class="cookie-banner" hidden>
  <div class="cookie-banner-inner">
    <div class="cookie-copy">
      <p><strong><?= e(t('cookie.title')) ?></strong></p>
      <p><?= e($cookieParts[0]) ?><a href="<?= $base ?>politica-de-privacidad/?lang=<?= e($lang) ?>"><?= e(t('cookie.privacy')) ?></a><?= e($cookieTail[0] ?? '') ?><a href="<?= $base ?>politica-de-cookies/?lang=<?= e($lang) ?>"><?= e(t('cookie.cookies')) ?></a><?= e($cookieTail[1] ?? '') ?></p>
    </div>
    <div class="cookie-actions">
      <button type="button" class="button button-small" data-cookie-ack><?= e(t('cookie.accept')) ?></button>
    </div>
  </div>
</div>
