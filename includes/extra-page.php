<?php
function campaign_form(int $id): void
{
    $markup = file_get_contents(__DIR__ . '/../content/form-' . $id . '.html');
    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $markup, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('//style | //script | //comment()') as $node) $node->parentNode->removeChild($node);
    foreach ($xpath->query('//form') as $form) {
        $form->setAttribute('action', 'https://mkt.apracom-ec.com/form/submit?formId=' . $id);
        $form->removeAttribute('novalidate');
    }
    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mauticform-required ")]') as $row) {
        $inputs = $xpath->query('.//input[not(@type="hidden") and not(@type="checkbox")] | .//select | .//textarea', $row);
        foreach ($inputs as $input) $input->setAttribute('required', 'required');
    }
    foreach ($xpath->query('//input | //select | //textarea') as $input) {
        $input->removeAttribute('style');
    }
    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " mauticform-checkboxgrp ")]') as $group) {
        $label = $xpath->query('./label', $group)->item(0);
        if ($label) {
            $labelId = $group->getAttribute('id') . '-label';
            $label->setAttribute('id', $labelId);
            $label->removeAttribute('for');
            $group->setAttribute('role', 'group');
            $group->setAttribute('aria-labelledby', $labelId);
        }
    }
    foreach ($xpath->query('//processing-instruction()') as $node) $node->parentNode->removeChild($node);
    echo $dom->saveHTML();
}
?>
<div class="resource-page">
<div class="container">
  <a class="resource-back" href="<?= $base ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg>Volver al inicio</a>
<?php if ($page === 'invitacion-de-webinar-2026'): ?>
  <article class="webinar-layout">
    <div class="webinar-summary">
      <h1>Patógenos bajo vigilancia:<br><i>Listeria monocytogenes</i> y <i>Salmonella</i> spp.</h1>
      <p class="webinar-date"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Zm2-2v5m10-5v5M3 10h18"/></svg><time datetime="2026-10-21">21 de octubre de 2026</time></p>
      <p class="webinar-speaker"><strong>Walter Morales</strong><span>Coordinador de Soporte Técnico</span></p>
      <section class="webinar-schedule" aria-labelledby="schedule-title">
        <h2 id="schedule-title">Horarios por país</h2>
        <dl><div><dt>10:00 AM</dt><dd>México / Centroamérica</dd></div><div><dt>11:00 AM</dt><dd>Ecuador / Colombia / Perú</dd></div><div><dt>12:00 PM</dt><dd>Venezuela / Bolivia / Rep. Dominicana</dd></div><div><dt>01:00 PM</dt><dd>Argentina / Uruguay / Brasil / Chile</dd></div></dl>
      </section>
      <a class="resource-contact" href="<?= $base ?>#Contactenos">Consultar a Pontelog</a>
    </div>
    <section class="registration-panel" aria-labelledby="registration-title">
      <div class="registration-intro"><h2 id="registration-title">Registro al webinar</h2><p>Completa tus datos para enviarlos al sistema de registro de Pontelog en <span>mkt.apracom-ec.com</span>.</p><p class="required-note">Los campos con * son obligatorios.</p></div>
      <?php campaign_form(13); ?>
    </section>
  </article>
<?php else: ?>
  <article class="campaign-layout">
    <header class="campaign-intro"><h1>Formulario Pontelog</h1><p>Comparte tus datos y las referencias de placas que necesitas consultar.</p></header>
    <div class="campaign-context"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v1"/></svg><p>Este formulario conserva las condiciones y fechas de la campaña original. Consulta con Pontelog la disponibilidad actual antes de realizar una solicitud.</p></div>
    <section class="registration-panel campaign-panel" aria-labelledby="campaign-form-title"><div class="registration-intro"><h2 id="campaign-form-title">Datos de la solicitud</h2><p>Al enviar, tus datos se dirigen al sistema de Pontelog en <span>mkt.apracom-ec.com</span>.</p><p class="required-note">Los campos con * son obligatorios.</p></div><?php campaign_form(3); ?></section>
  </article>
<?php endif; ?>
</div>
</div>
