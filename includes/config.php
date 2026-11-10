<?php
declare(strict_types=1);

/**
 * Carga configuración local (config.local.php) y variables de entorno.
 * Prioridad: getenv / $_ENV > config.local.php > valores por defecto.
 */
function pontelog_config(): array
{
    static $config = null;
    if (is_array($config)) {
        return $config;
    }

    $defaults = [
        'mail_enabled' => false,
        'contact_email' => 'info@pontelog.com',
        'mail_from' => '',
        'mail_from_name' => 'Pontelog',
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_secure' => 'tls', // tls | ssl | ''
        'data_dir' => '',
    ];

    $local = [];
    $localPath = dirname(__DIR__) . '/config.local.php';
    if (is_file($localPath)) {
        $loaded = require $localPath;
        if (is_array($loaded)) {
            $local = $loaded;
        }
    }

    $config = array_merge($defaults, $local);

    $envMap = [
        'PONTELOG_MAIL_ENABLED' => 'mail_enabled',
        'PONTELOG_CONTACT_EMAIL' => 'contact_email',
        'PONTELOG_MAIL_FROM' => 'mail_from',
        'PONTELOG_MAIL_FROM_NAME' => 'mail_from_name',
        'PONTELOG_SMTP_HOST' => 'smtp_host',
        'PONTELOG_SMTP_PORT' => 'smtp_port',
        'PONTELOG_SMTP_USER' => 'smtp_user',
        'PONTELOG_SMTP_PASS' => 'smtp_pass',
        'PONTELOG_SMTP_SECURE' => 'smtp_secure',
        'PONTELOG_DATA_DIR' => 'data_dir',
    ];

    foreach ($envMap as $env => $key) {
        $value = getenv($env);
        if ($value === false || $value === '') {
            continue;
        }
        if ($key === 'mail_enabled') {
            $config[$key] = $value === '1' || strcasecmp($value, 'true') === 0;
        } elseif ($key === 'smtp_port') {
            $config[$key] = (int) $value;
        } else {
            $config[$key] = $value;
        }
    }

    if ($config['mail_from'] === '') {
        $config['mail_from'] = is_string($config['smtp_user']) && $config['smtp_user'] !== ''
            ? $config['smtp_user']
            : (string) $config['contact_email'];
    }

    return $config;
}
