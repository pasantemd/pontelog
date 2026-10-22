<?php
declare(strict_types=1);

function csrf_token(array &$session): string
{
    if (!isset($session['contact_csrf']) || !is_string($session['contact_csrf'])) {
        $session['contact_csrf'] = bin2hex(random_bytes(32));
    }
    return $session['contact_csrf'];
}

function handle_contact(array $post, array &$session): array
{
    $values = [];
    foreach (['name', 'email', 'phone', 'message'] as $field) {
        $values[$field] = isset($post[$field]) && is_string($post[$field]) ? trim($post[$field]) : '';
    }
    $values['newsletter'] = isset($post['newsletter']) && $post['newsletter'] === '1';
    $result = ['status' => 'error', 'message' => '', 'errors' => [], 'values' => $values];
    if ($post === []) {
        $result['status'] = 'idle';
        return $result;
    }
    if (!is_string($post['csrf'] ?? null) || !hash_equals(csrf_token($session), $post['csrf'])) {
        $result['message'] = 'La sesión del formulario caducó. Recarga la página e inténtalo de nuevo.';
        return $result;
    }
    if (!is_string($post['website'] ?? '') || ($post['website'] ?? '') !== '') {
        $result['message'] = 'No se pudo registrar la consulta. Inténtalo de nuevo.';
        return $result;
    }
    if (time() - (int) ($session['contact_last_sent'] ?? 0) < 30) {
        $result['message'] = 'Espera 30 segundos antes de enviar otra consulta.';
        return $result;
    }
    if ($values['name'] === '' || strlen($values['name']) > 200) {
        $result['errors']['name'] = 'Escribe tu nombre o empresa (máximo 200 caracteres).';
    }
    if (strlen($values['email']) > 254 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $result['errors']['email'] = 'Escribe un correo electrónico válido.';
    }
    if (!preg_match('/^[+()0-9 .-]{7,30}$/D', $values['phone'])) {
        $result['errors']['phone'] = 'Escribe un teléfono válido de 7 a 30 caracteres.';
    }
    if (strlen($values['message']) > 5000) {
        $result['errors']['message'] = 'El mensaje no puede superar los 5000 caracteres.';
    }
    if (isset($post['newsletter']) && !in_array($post['newsletter'], ['0', '1'], true)) {
        $result['errors']['newsletter'] = 'Selecciona una opción válida para el boletín.';
    }
    if ($result['errors']) {
        $result['message'] = 'Revisa los campos indicados antes de enviar.';
        return $result;
    }

    $directory = getenv('PONTELOG_DATA_DIR') ?: dirname(__DIR__, 3) . '/pontelog-data';
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        $result['message'] = 'No se pudo guardar la consulta. Escríbenos a info@pontelog.com.';
        return $result;
    }
    $directory = realpath($directory);
    $webroot = realpath($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__));
    $normalizedDirectory = strtolower(str_replace('\\', '/', (string) $directory)) . '/';
    $normalizedWebroot = strtolower(str_replace('\\', '/', (string) $webroot)) . '/';
    if (!$directory || ($webroot && str_starts_with($normalizedDirectory, $normalizedWebroot))) {
        $result['message'] = 'El formulario necesita configurar un directorio privado. Escríbenos a info@pontelog.com.';
        return $result;
    }
    $record = ['id' => bin2hex(random_bytes(12)), 'created_at' => gmdate('c')] + $values;
    $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL;
    if (@file_put_contents($directory . '/consultas.jsonl', $line, FILE_APPEND | LOCK_EX) !== strlen($line)) {
        $result['message'] = 'No se pudo guardar la consulta. Escríbenos a info@pontelog.com.';
        return $result;
    }
    @chmod($directory . '/consultas.jsonl', 0600);
    $session['contact_last_sent'] = time();
    $recipient = getenv('PONTELOG_CONTACT_EMAIL');
    $sent = false;
    if (getenv('PONTELOG_MAIL_ENABLED') === '1' && is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $body = "Nombre / Empresa: {$values['name']}\nCorreo: {$values['email']}\nTeléfono: {$values['phone']}\n\n{$values['message']}\n\nSolicitud de boletín: " . ($values['newsletter'] ? 'Sí' : 'No');
        $sent = @mail($recipient, 'Consulta desde Pontelog', $body, [
            'Reply-To' => $values['email'],
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
    $result['status'] = 'success';
    $result['message'] = $sent
        ? 'Tu consulta se guardó y se entregó al servidor de correo para su envío.'
        : 'Tu consulta se guardó en este sitio. El envío de correo no está disponible; para contacto directo escribe a info@pontelog.com.';
    $result['values'] = ['name' => '', 'email' => '', 'phone' => '', 'message' => '', 'newsletter' => false];
    return $result;
}
