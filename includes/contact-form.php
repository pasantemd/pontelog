<?php
$newsletterParts = explode(':privacy', t('contact.newsletter'), 2);
?>
<form class="contact-form" method="post" action="<?= $base ?>?lang=<?= e($lang) ?>#Contactenos">
  <h3><?= e(t('contact.form_title')) ?></h3>
  <p class="form-intro"><?= e(t('contact.form_intro')) ?></p>
  <p id="contact-selection" class="contact-selection" role="status" hidden></p>
  <?php if ($contact['status'] !== 'idle'): ?><div class="form-feedback <?= e($contact['status']) ?>" role="<?= $contact['status'] === 'error' ? 'alert' : 'status' ?>"><?= e($contact['message']) ?></div><?php endif; ?>
  <input type="hidden" name="csrf" value="<?= e($token) ?>">
  <div class="honeypot" aria-hidden="true"><label><?= e(t('contact.honeypot')) ?><input name="website" tabindex="-1" autocomplete="off"></label></div>
  <div class="form-fields">
  <?php foreach (['name' => ['contact.name', 'text', 'organization', 150], 'email' => ['contact.email', 'email', 'email', 254], 'phone' => ['contact.phone', 'tel', 'tel', 30]] as $field => $spec): ?>
  <div class="field field-<?= $field ?>"><label for="<?= $field ?>"><?= e(t($spec[0])) ?> <span aria-hidden="true">*</span></label><input id="<?= $field ?>" name="<?= $field ?>" type="<?= $spec[1] ?>" autocomplete="<?= $spec[2] ?>" maxlength="<?= $spec[3] ?>" value="<?= e((string)($contact['values'][$field] ?? '')) ?>" required <?= isset($contact['errors'][$field]) ? 'aria-invalid="true" aria-describedby="error-' . $field . '"' : '' ?>><?php if (isset($contact['errors'][$field])): ?><p class="field-error" id="error-<?= $field ?>"><?= e($contact['errors'][$field]) ?></p><?php endif; ?></div>
  <?php endforeach; ?>
  <div class="field field-message"><label for="message"><?= e(t('contact.message')) ?> <span aria-hidden="true">*</span></label><textarea id="message" name="message" rows="4" minlength="10" maxlength="5000" required placeholder="<?= e(t('contact.message_ph')) ?>" <?= isset($contact['errors']['message']) ? 'aria-invalid="true" aria-describedby="error-message"' : '' ?>><?= e((string)($contact['values']['message'] ?? '')) ?></textarea><?php if(isset($contact['errors']['message'])): ?><p class="field-error" id="error-message"><?= e($contact['errors']['message']) ?></p><?php endif; ?></div>
  </div>
  <label class="newsletter"><input type="checkbox" name="newsletter" value="1" <?= !empty($contact['values']['newsletter']) ? 'checked' : '' ?>><span><?= e($newsletterParts[0]) ?><a href="<?= $base ?>politica-de-privacidad/?lang=<?= e($lang) ?>"><?= e(t('contact.privacy_link')) ?></a><?= e($newsletterParts[1] ?? '') ?></span></label>
  <div class="form-submit"><button class="button submit" type="submit"><?= e(t('contact.submit')) ?> <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></button><span><?= e(t('contact.required')) ?></span></div>
</form>
