<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/mautic.php';

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
        $result['message'] = t('msg.csrf');
        return $result;
    }
    if (!is_string($post['website'] ?? '') || ($post['website'] ?? '') !== '') {
        $result['message'] = t('msg.bot');
        return $result;
    }
    if (time() - (int) ($session['contact_last_sent'] ?? 0) < 30) {
        $result['message'] = t('msg.rate');
        return $result;
    }
    if ($values['name'] === '' || strlen($values['name']) > 200) {
        $result['errors']['name'] = t('msg.name');
    }
    if (strlen($values['email']) > 254 || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $result['errors']['email'] = t('msg.email');
    }
    if (!preg_match('/^[+()0-9 .-]{7,30}$/D', $values['phone'])) {
        $result['errors']['phone'] = t('msg.phone');
    }
    if (strlen($values['message']) > 5000) {
        $result['errors']['message'] = t('msg.message');
    }
    if (isset($post['newsletter']) && !in_array($post['newsletter'], ['0', '1'], true)) {
        $result['errors']['newsletter'] = t('msg.newsletter');
    }
    if ($result['errors']) {
        $result['message'] = t('msg.review');
        return $result;
    }

    $config = pontelog_config();
    $directory = $config['data_dir'] !== ''
        ? (string) $config['data_dir']
        : dirname(__DIR__, 2) . '/pontelog-data';
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        $result['message'] = t('msg.save_fail');
        return $result;
    }
    $directory = realpath($directory);
    $webroot = realpath($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__));
    $normalizedDirectory = strtolower(str_replace('\\', '/', (string) $directory)) . '/';
    $normalizedWebroot = strtolower(str_replace('\\', '/', (string) $webroot)) . '/';
    if (!$directory || ($webroot && str_starts_with($normalizedDirectory, $normalizedWebroot))) {
        $result['message'] = t('msg.data_dir');
        return $result;
    }
    $record = ['id' => bin2hex(random_bytes(12)), 'created_at' => gmdate('c')] + $values;
    $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL;
    if (@file_put_contents($directory . '/consultas.jsonl', $line, FILE_APPEND | LOCK_EX) !== strlen($line)) {
        $result['message'] = t('msg.save_fail');
        return $result;
    }
    @chmod($directory . '/consultas.jsonl', 0600);
    $session['contact_last_sent'] = time();

    $mailResult = send_contact_mail($values, $config);
    $mauticResult = subscribe_mautic_newsletter($values, $config);
    $result['status'] = 'success';
    if ($mailResult['ok']) {
        $result['message'] = t('msg.mail_ok');
    } else {
        $result['message'] = t('msg.mail_saved');
        if (!empty($config['mail_debug']) && $mailResult['error'] !== '') {
            $result['message'] .= ' [DEBUG correo: ' . $mailResult['error'] . ']';
        }
    }
    if ($values['newsletter']) {
        if ($mauticResult['ok'] && !$mauticResult['skipped']) {
            $result['message'] .= t('msg.newsletter_ok');
        } elseif (!$mauticResult['skipped'] || $mauticResult['error'] !== '') {
            if (!empty($config['mail_debug']) && $mauticResult['error'] !== '') {
                $result['message'] .= ' [DEBUG boletín: ' . $mauticResult['error'] . ']';
            }
        }
    }
    $result['values'] = ['name' => '', 'email' => '', 'phone' => '', 'message' => '', 'newsletter' => false];
    return $result;
}
