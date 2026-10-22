<form class="contact-form" method="post" action="<?= $base ?>#Contactenos">
  <h3>Cuéntanos qué necesitas</h3>
  <p class="form-intro">Déjanos tus datos y la solución que buscas.</p>
  <p id="contact-selection" class="contact-selection" role="status" hidden></p>
  <?php if ($contact['status'] !== 'idle'): ?><div class="form-feedback <?= e($contact['status']) ?>" role="<?= $contact['status'] === 'error' ? 'alert' : 'status' ?>"><?= e($contact['message']) ?></div><?php endif; ?>
  <input type="hidden" name="csrf" value="<?= e($token) ?>">
  <div class="honeypot" aria-hidden="true"><label>Dejar vacío<input name="website" tabindex="-1" autocomplete="off"></label></div>
  <div class="form-fields">
  <?php foreach (['name' => ['Nombre / Empresa', 'text', 'organization', 150], 'email' => ['Correo electrónico', 'email', 'email', 254], 'phone' => ['Teléfono', 'tel', 'tel', 30]] as $field => $spec): ?>
  <div class="field field-<?= $field ?>"><label for="<?= $field ?>"><?= $spec[0] ?> <span aria-hidden="true">*</span></label><input id="<?= $field ?>" name="<?= $field ?>" type="<?= $spec[1] ?>" autocomplete="<?= $spec[2] ?>" maxlength="<?= $spec[3] ?>" value="<?= e((string)($contact['values'][$field] ?? '')) ?>" required <?= isset($contact['errors'][$field]) ? 'aria-invalid="true" aria-describedby="error-' . $field . '"' : '' ?>><?php if (isset($contact['errors'][$field])): ?><p class="field-error" id="error-<?= $field ?>"><?= e($contact['errors'][$field]) ?></p><?php endif; ?></div>
  <?php endforeach; ?>
  <div class="field field-message"><label for="message">Comentario o mensaje</label><textarea id="message" name="message" rows="4" maxlength="5000" placeholder="¿Qué necesitas analizar o controlar?"><?= e((string)($contact['values']['message'] ?? '')) ?></textarea><?php if(isset($contact['errors']['message'])): ?><p class="field-error"><?= e($contact['errors']['message']) ?></p><?php endif; ?></div>
  </div>
  <label class="newsletter"><input type="checkbox" name="newsletter" value="1" <?= !empty($contact['values']['newsletter']) ? 'checked' : '' ?>><span>Quiero suscribirme al boletín de Pontelog.</span></label>
  <div class="form-submit"><button class="button submit" type="submit">Enviar consulta <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></button><span>Los campos con * son obligatorios.</span></div>
</form>
