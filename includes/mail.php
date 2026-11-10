<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__) . '/lib/PHPMailer/src/Exception.php';
require_once dirname(__DIR__) . '/lib/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/lib/PHPMailer/src/SMTP.php';

/**
 * Envía la consulta de contacto por SMTP con PHPMailer.
 * Devuelve true solo si el servidor SMTP aceptó el mensaje.
 *
 * @param array{name:string,email:string,phone:string,message:string,newsletter:bool} $values
 */
function send_contact_mail(array $values, array $config): bool
{
    if (empty($config['mail_enabled'])) {
        return false;
    }

    $recipient = $config['contact_email'] ?? '';
    if (!is_string($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $host = trim((string) ($config['smtp_host'] ?? ''));
    if ($host === '') {
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = (int) ($config['smtp_port'] ?? 587);
        $mail->SMTPAuth = true;
        $mail->Username = (string) ($config['smtp_user'] ?? '');
        $mail->Password = (string) ($config['smtp_pass'] ?? '');

        $secure = strtolower(trim((string) ($config['smtp_secure'] ?? 'tls')));
        if ($secure === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($secure === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = '';
            $mail->SMTPAutoTLS = false;
        }

        $from = (string) ($config['mail_from'] ?? $recipient);
        $fromName = (string) ($config['mail_from_name'] ?? 'Pontelog');
        $mail->setFrom($from, $fromName);
        $mail->addAddress($recipient);
        $mail->addReplyTo($values['email'], $values['name']);

        $newsletter = !empty($values['newsletter']) ? 'Sí' : 'No';
        $mail->Subject = 'Consulta desde Pontelog';
        $mail->Body = "Nombre / Empresa: {$values['name']}\n"
            . "Correo: {$values['email']}\n"
            . "Teléfono: {$values['phone']}\n\n"
            . "{$values['message']}\n\n"
            . "Solicitud de boletín: {$newsletter}\n";
        $mail->AltBody = $mail->Body;

        return $mail->send();
    } catch (MailException $e) {
        error_log('Pontelog mail error: ' . $mail->ErrorInfo);
        return false;
    }
}
