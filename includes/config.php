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
        'mail_copy' => '', // copia visible (CC), uno o varios separados por coma
        'mail_from' => '',
        'mail_from_name' => 'Pontelog',
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_secure' => 'tls', // tls | ssl | ''
        'mail_debug' => false, // true = muestra el error SMTP en el formulario
        'data_dir' => '',
        'mautic_enabled' => false,
        'mautic_base_url' => 'https://mkt.apracom-ec.com',
        'mautic_client_id' => '',
        'mautic_client_secret' => '',
        'mautic_segment_id' => '20',
        'mautic_form_id' => '14',
        'mautic_form_name' => 'boletinpontelogweb',
        'mautic_email_field' => 'email',
        'mautic_name_field' => 'nombre',
        'mautic_phone_field' => 'telefono',
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
        'PONTELOG_MAIL_COPY' => 'mail_copy',
        'PONTELOG_MAIL_FROM' => 'mail_from',
        'PONTELOG_MAIL_FROM_NAME' => 'mail_from_name',
        'PONTELOG_SMTP_HOST' => 'smtp_host',
        'PONTELOG_SMTP_PORT' => 'smtp_port',
        'PONTELOG_SMTP_USER' => 'smtp_user',
        'PONTELOG_SMTP_PASS' => 'smtp_pass',
        'PONTELOG_SMTP_SECURE' => 'smtp_secure',
        'PONTELOG_MAIL_DEBUG' => 'mail_debug',
        'PONTELOG_DATA_DIR' => 'data_dir',
        'PONTELOG_MAUTIC_ENABLED' => 'mautic_enabled',
        'PONTELOG_MAUTIC_BASE_URL' => 'mautic_base_url',
        'PONTELOG_MAUTIC_CLIENT_ID' => 'mautic_client_id',
        'PONTELOG_MAUTIC_CLIENT_SECRET' => 'mautic_client_secret',
        'PONTELOG_MAUTIC_SEGMENT_ID' => 'mautic_segment_id',
        'PONTELOG_MAUTIC_FORM_ID' => 'mautic_form_id',
        'PONTELOG_MAUTIC_FORM_NAME' => 'mautic_form_name',
        'PONTELOG_MAUTIC_EMAIL_FIELD' => 'mautic_email_field',
        'PONTELOG_MAUTIC_NAME_FIELD' => 'mautic_name_field',
        'PONTELOG_MAUTIC_PHONE_FIELD' => 'mautic_phone_field',
    ];

    foreach ($envMap as $env => $key) {
        $value = getenv($env);
        if ($value === false || $value === '') {
            continue;
        }
        if ($key === 'mail_enabled' || $key === 'mail_debug' || $key === 'mautic_enabled') {
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
