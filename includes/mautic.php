<?php
declare(strict_types=1);

/**
 * Suscribe al boletín en Mautic.
 * Prioridad: API OAuth2 (contactos + segmento) → formulario público /form/submit.
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
    if ($base === '') {
        return ['ok' => false, 'skipped' => true, 'error' => 'mautic_base_url está vacío'];
    }

    $clientId = trim((string) ($config['mautic_client_id'] ?? ''));
    $clientSecret = trim((string) ($config['mautic_client_secret'] ?? ''));
    if ($clientId !== '' && $clientSecret !== '' && $clientSecret !== 'TU_SECRET_MAUTIC') {
        $api = mautic_subscribe_via_api($values, $config, $base, $clientId, $clientSecret);
        if ($api['ok'] || empty($config['mautic_form_id'])) {
            return $api;
        }
        // Si la API falla y hay formulario, intentamos el endpoint público como respaldo.
        log_mautic_error($config, 'API falló, probando formulario: ' . $api['error']);
    }

    return mautic_subscribe_via_form($values, $config, $base);
}

/**
 * @param array{name:string,email:string,phone:string,message:string,newsletter:bool} $values
 * @return array{ok:bool,skipped:bool,error:string}
 */
function mautic_subscribe_via_api(array $values, array $config, string $base, string $clientId, string $clientSecret): array
{
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'skipped' => false, 'error' => 'PHP curl no está disponible'];
    }

    $token = mautic_oauth_token($base, $clientId, $clientSecret, $config);
    if ($token === null) {
        return ['ok' => false, 'skipped' => false, 'error' => 'No se pudo obtener access_token OAuth2 de Mautic'];
    }

    $payload = [
        'email' => $values['email'],
        'firstname' => $values['name'],
        'overwriteWithBlank' => false,
        'tags' => ['boletin-pontelog-web'],
    ];
    if ($values['phone'] !== '') {
        $payload['phone'] = $values['phone'];
    }

    $created = mautic_api_request('POST', $base . '/api/contacts/new', $token, $payload);
    if (!$created['ok']) {
        return ['ok' => false, 'skipped' => false, 'error' => 'Crear contacto: ' . $created['error']];
    }

    $contactId = (int) (($created['json']['contact']['id'] ?? 0));
    if ($contactId < 1) {
        return ['ok' => false, 'skipped' => false, 'error' => 'Mautic no devolvió ID de contacto'];
    }

    $segmentId = trim((string) ($config['mautic_segment_id'] ?? ''));
    if ($segmentId !== '' && ctype_digit($segmentId)) {
        $added = mautic_api_request('POST', $base . '/api/segments/' . $segmentId . '/contact/' . $contactId . '/add', $token, null);
        if (!$added['ok']) {
            return ['ok' => false, 'skipped' => false, 'error' => 'Contacto creado pero no se agregó al segmento: ' . $added['error']];
        }
    }

    return ['ok' => true, 'skipped' => false, 'error' => ''];
}

/**
 * @param array{name:string,email:string,phone:string,message:string,newsletter:bool} $values
 * @return array{ok:bool,skipped:bool,error:string}
 */
function mautic_subscribe_via_form(array $values, array $config, string $base): array
{
    $formId = trim((string) ($config['mautic_form_id'] ?? ''));
    $formName = trim((string) ($config['mautic_form_name'] ?? ''));
    $emailField = trim((string) ($config['mautic_email_field'] ?? 'email'));
    $nameField = trim((string) ($config['mautic_name_field'] ?? 'nombre'));
    $phoneField = trim((string) ($config['mautic_phone_field'] ?? 'telefono'));

    if ($formId === '' || !ctype_digit($formId)) {
        return ['ok' => false, 'skipped' => true, 'error' => 'Configura mautic_form_id o las claves API'];
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
        return ['ok' => false, 'skipped' => false, 'error' => 'PHP curl no está disponible'];
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
        $message = 'Error curl Mautic form: ' . $error;
        log_mautic_error($config, $message);
        return ['ok' => false, 'skipped' => false, 'error' => $message];
    }
    if ($status < 200 || $status >= 400) {
        $message = 'Mautic form HTTP ' . $status;
        log_mautic_error($config, $message . ' body=' . substr((string) $body, 0, 300));
        return ['ok' => false, 'skipped' => false, 'error' => $message];
    }

    return ['ok' => true, 'skipped' => false, 'error' => ''];
}

function mautic_oauth_token(string $base, string $clientId, string $clientSecret, array $config): ?string
{
    $cacheFile = mautic_token_cache_path($config);
    if ($cacheFile && is_file($cacheFile)) {
        $cached = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($cached)
            && !empty($cached['access_token'])
            && !empty($cached['expires_at'])
            && (int) $cached['expires_at'] > time() + 30) {
            return (string) $cached['access_token'];
        }
    }

    $ch = curl_init($base . '/oauth/v2/token');
    if ($ch === false) {
        return null;
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!is_string($body) || $status < 200 || $status >= 300) {
        log_mautic_error($config, 'OAuth token HTTP ' . $status . ' body=' . substr((string) $body, 0, 300));
        return null;
    }
    $json = json_decode($body, true);
    if (!is_array($json) || empty($json['access_token'])) {
        log_mautic_error($config, 'OAuth token inválido');
        return null;
    }

    if ($cacheFile) {
        $ttl = max(60, (int) ($json['expires_in'] ?? 3600) - 60);
        @file_put_contents($cacheFile, json_encode([
            'access_token' => $json['access_token'],
            'expires_at' => time() + $ttl,
        ], JSON_UNESCAPED_SLASHES));
        @chmod($cacheFile, 0600);
    }

    return (string) $json['access_token'];
}

/**
 * @return array{ok:bool,error:string,json:array<string,mixed>|null}
 */
function mautic_api_request(string $method, string $url, string $token, ?array $payload): array
{
    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'error' => 'curl_init falló', 'json' => null];
    }
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
    ];
    $opts = [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ];
    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
        $opts[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
    $opts[CURLOPT_HTTPHEADER] = $headers;
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        return ['ok' => false, 'error' => $error, 'json' => null];
    }
    $json = is_string($body) ? json_decode($body, true) : null;
    if ($status < 200 || $status >= 300) {
        $detail = is_array($json) ? json_encode($json, JSON_UNESCAPED_UNICODE) : substr((string) $body, 0, 300);
        return ['ok' => false, 'error' => 'HTTP ' . $status . ' ' . $detail, 'json' => is_array($json) ? $json : null];
    }
    return ['ok' => true, 'error' => '', 'json' => is_array($json) ? $json : null];
}

function mautic_token_cache_path(array $config): ?string
{
    $directory = trim((string) ($config['data_dir'] ?? ''));
    if ($directory === '') {
        $directory = dirname(__DIR__, 2) . '/pontelog-data';
    }
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        return null;
    }
    return rtrim($directory, '/\\') . '/mautic-oauth-cache.json';
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
