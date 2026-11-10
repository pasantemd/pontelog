<?php
declare(strict_types=1);

/**
 * Envía la suscripción al boletín hacia un formulario publicado de Mautic.
 *
 * @param array{name:string,email:string,phone:string,message:string,newsletter:bool} $values
 * @return array{ok:bool,skipped:bool,error:string}
 */
function subscribe_mautic_newsletter(array $values, array $config): array
{
    if (empty($values['newsletter'])) {
        return ['ok' => true, 'skipped' => true, 'error' => ''];
    }
    if (empty($config['mautic_enabled'])) {
        return ['ok' => false, 'skipped' => true, 'error' => 'mautic_enabled está en false'];
    }

    $base = rtrim((string) ($config['mautic_base_url'] ?? ''), '/');
    $formId = trim((string) ($config['mautic_form_id'] ?? ''));
    $formName = trim((string) ($config['mautic_form_name'] ?? ''));
    $emailField = trim((string) ($config['mautic_email_field'] ?? 'email'));
    $nameField = trim((string) ($config['mautic_name_field'] ?? 'nombre'));
    $phoneField = trim((string) ($config['mautic_phone_field'] ?? ''));

    if ($base === '' || $formId === '' || !ctype_digit($formId)) {
        return ['ok' => false, 'skipped' => true, 'error' => 'Configura mautic_base_url y mautic_form_id en config.local.php'];
    }
    if ($emailField === '') {
        return ['ok' => false, 'skipped' => true, 'error' => 'mautic_email_field está vacío'];
    }

    $payload = [
        'mauticform[' . $emailField . ']' => $values['email'],
        'mauticform[formId]' => $formId,
        'mauticform[return]' => '',
        'mauticform[submit]' => '1',
    ];
    if ($formName !== '') {
        $payload['mauticform[formName]'] = $formName;
    }
    if ($nameField !== '' && $values['name'] !== '') {
        $payload['mauticform[' . $nameField . ']'] = $values['name'];
    }
    if ($phoneField !== '' && $values['phone'] !== '') {
        $payload['mauticform[' . $phoneField . ']'] = $values['phone'];
    }

    $url = $base . '/form/submit?formId=' . rawurlencode($formId);
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'skipped' => false, 'error' => 'PHP curl no está disponible en el servidor'];
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'skipped' => false, 'error' => 'No se pudo iniciar curl hacia Mautic'];
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: text/html,application/xhtml+xml',
        ],
        CURLOPT_USERAGENT => 'PontelogContactForm/1.0',
    ]);

    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        $message = 'Error curl Mautic: ' . $error;
        error_log('Pontelog mautic error: ' . $message);
        log_mautic_error($config, $message);
        return ['ok' => false, 'skipped' => false, 'error' => $message];
    }
    if ($status < 200 || $status >= 400) {
        $message = 'Mautic respondió HTTP ' . $status;
        error_log('Pontelog mautic error: ' . $message);
        log_mautic_error($config, $message . ' body=' . substr((string) $body, 0, 300));
        return ['ok' => false, 'skipped' => false, 'error' => $message];
    }

    return ['ok' => true, 'skipped' => false, 'error' => ''];
}

function log_mautic_error(array $config, string $error): void
{
    $directory = trim((string) ($config['data_dir'] ?? ''));
    if ($directory === '') {
        $directory = dirname(__DIR__, 2) . '/pontelog-data';
    }
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        return;
    }
    $line = '[' . gmdate('c') . '] ' . str_replace(["\r", "\n"], ['', ' | '], $error) . PHP_EOL;
    @file_put_contents(rtrim($directory, '/\\') . '/mautic-errors.log', $line, FILE_APPEND | LOCK_EX);
}
