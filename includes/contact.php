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

function csrf_rotate(array &$session): void
{
    $session['contact_csrf'] = bin2hex(random_bytes(32));
}

function pontelog_client_ip(): string
{
    $candidates = [];
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $candidates[] = (string) $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);
        $candidates[] = trim((string) ($parts[0] ?? ''));
    }
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $candidates[] = (string) $_SERVER['REMOTE_ADDR'];
    }
    foreach ($candidates as $ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    return '0.0.0.0';
}

/**
 * Resuelve un directorio de datos privado fuera del document root.
 */
function pontelog_private_data_dir(array $config): ?string
{
    $directory = $config['data_dir'] !== ''
        ? (string) $config['data_dir']
        : dirname(__DIR__, 2) . '/pontelog-data';
    if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
        return null;
    }
    $directory = realpath($directory);
    $webroot = realpath($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__));
    if (!$directory) {
        return null;
    }
    if ($webroot) {
        $normalizedDirectory = strtolower(str_replace('\\', '/', $directory)) . '/';
        $normalizedWebroot = strtolower(str_replace('\\', '/', $webroot)) . '/';
        if (str_starts_with($normalizedDirectory, $normalizedWebroot)) {
            return null;
        }
    }
    return $directory;
}

/**
 * Límite por IP: 1 cada 30 s, 5/hora, 15/día.
 *
 * @return array{ok:bool,reason:string}
 */
function pontelog_ip_rate_check(string $directory, string $ip): array
{
    $path = $directory . '/rate-limit.json';
    $now = time();
    $key = hash('sha256', $ip);
    $handle = @fopen($path, 'c+');
    if ($handle === false) {
        return ['ok' => true, 'reason' => ''];
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return ['ok' => true, 'reason' => ''];
        }
        $raw = stream_get_contents($handle);
        $data = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        if (!is_array($data)) {
            $data = [];
        }

        $entry = is_array($data[$key] ?? null) ? $data[$key] : [
            'last' => 0,
            'hour_start' => $now,
            'hour_count' => 0,
            'day_start' => $now,
            'day_count' => 0,
        ];

        $last = (int) ($entry['last'] ?? 0);
        $hourStart = (int) ($entry['hour_start'] ?? $now);
        $hourCount = (int) ($entry['hour_count'] ?? 0);
        $dayStart = (int) ($entry['day_start'] ?? $now);
        $dayCount = (int) ($entry['day_count'] ?? 0);

        if ($now - $hourStart >= 3600) {
            $hourStart = $now;
            $hourCount = 0;
        }
        if ($now - $dayStart >= 86400) {
            $dayStart = $now;
            $dayCount = 0;
        }

        if ($last > 0 && ($now - $last) < 30) {
            return ['ok' => false, 'reason' => 'burst'];
        }
        if ($hourCount >= 5) {
            return ['ok' => false, 'reason' => 'hour'];
        }
        if ($dayCount >= 15) {
            return ['ok' => false, 'reason' => 'day'];
        }

        // Persist cleaned windows even on check so file stays small later via record.
        $data[$key] = [
            'last' => $last,
            'hour_start' => $hourStart,
            'hour_count' => $hourCount,
            'day_start' => $dayStart,
            'day_count' => $dayCount,
        ];
        // Prune stale keys older than 2 days.
        foreach ($data as $k => $row) {
            if (!is_array($row)) {
                unset($data[$k]);
                continue;
            }
            $rowLast = (int) ($row['last'] ?? 0);
            $rowDay = (int) ($row['day_start'] ?? 0);
            if ($rowLast > 0 && ($now - max($rowLast, $rowDay)) > 172800) {
                unset($data[$k]);
            }
        }
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($data, JSON_UNESCAPED_SLASHES));
        fflush($handle);
        @chmod($path, 0600);
        return ['ok' => true, 'reason' => ''];
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function pontelog_ip_rate_record(string $directory, string $ip): void
{
    $path = $directory . '/rate-limit.json';
    $now = time();
    $key = hash('sha256', $ip);
    $handle = @fopen($path, 'c+');
    if ($handle === false) {
        return;
    }
    try {
        if (!flock($handle, LOCK_EX)) {
            return;
        }
        $raw = stream_get_contents($handle);
        $data = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        if (!is_array($data)) {
            $data = [];
        }
        $entry = is_array($data[$key] ?? null) ? $data[$key] : [
            'last' => 0,
            'hour_start' => $now,
            'hour_count' => 0,
            'day_start' => $now,
            'day_count' => 0,
        ];
        $hourStart = (int) ($entry['hour_start'] ?? $now);
        $hourCount = (int) ($entry['hour_count'] ?? 0);
        $dayStart = (int) ($entry['day_start'] ?? $now);
        $dayCount = (int) ($entry['day_count'] ?? 0);
        if ($now - $hourStart >= 3600) {
            $hourStart = $now;
            $hourCount = 0;
        }
        if ($now - $dayStart >= 86400) {
            $dayStart = $now;
            $dayCount = 0;
        }
        $data[$key] = [
            'last' => $now,
            'hour_start' => $hourStart,
            'hour_count' => $hourCount + 1,
            'day_start' => $dayStart,
            'day_count' => $dayCount + 1,
        ];
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode($data, JSON_UNESCAPED_SLASHES));
        fflush($handle);
        @chmod($path, 0600);
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
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

    $config = pontelog_config();
    $directory = pontelog_private_data_dir($config);
    if ($directory === null) {
        $result['message'] = t('msg.data_dir');
        return $result;
    }

    $ipCheck = pontelog_ip_rate_check($directory, pontelog_client_ip());
    if (!$ipCheck['ok']) {
        $result['message'] = match ($ipCheck['reason']) {
            'hour' => t('msg.rate_hour'),
            'day' => t('msg.rate_day'),
            default => t('msg.rate'),
        };
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
    if ($values['message'] === '' || strlen($values['message']) < 10) {
        $result['errors']['message'] = t('msg.message_short');
    } elseif (strlen($values['message']) > 5000) {
        $result['errors']['message'] = t('msg.message');
    }
    if (isset($post['newsletter']) && !in_array($post['newsletter'], ['0', '1'], true)) {
        $result['errors']['newsletter'] = t('msg.newsletter');
    }
    if ($result['errors']) {
        $result['message'] = t('msg.review');
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
    pontelog_ip_rate_record($directory, pontelog_client_ip());
    csrf_rotate($session);

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
