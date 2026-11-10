<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__) . '/lib/PHPMailer/src/Exception.php';
require_once dirname(__DIR__) . '/lib/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/lib/PHPMailer/src/SMTP.php';

/**
 * Envía la consulta de contacto por SMTP con PHPMailer (HTML + texto).
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
        foreach (parse_mail_copy_list($config['mail_copy'] ?? '') as $copy) {
            if (strcasecmp($copy, $recipient) !== 0) {
                $mail->addBCC($copy);
            }
        }
        $mail->addReplyTo($values['email'], $values['name']);

        $newsletter = !empty($values['newsletter']) ? 'Sí' : 'No';
        $mail->Subject = 'Nueva consulta desde Pontelog';
        $mail->isHTML(true);
        $mail->Body = build_contact_mail_html($values, $newsletter);
        $mail->AltBody = build_contact_mail_text($values, $newsletter);

        return $mail->send();
    } catch (MailException $e) {
        error_log('Pontelog mail error: ' . $mail->ErrorInfo);
        return false;
    }
}

/** @return list<string> */
function parse_mail_copy_list(mixed $copy): array
{
    if (is_array($copy)) {
        $parts = $copy;
    } elseif (is_string($copy) && $copy !== '') {
        $parts = preg_split('/[\s,;]+/', $copy) ?: [];
    } else {
        return [];
    }

    $emails = [];
    foreach ($parts as $part) {
        if (!is_string($part)) {
            continue;
        }
        $email = trim($part);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emails[$email] = $email;
        }
    }
    return array_values($emails);
}

/**
 * @param array{name:string,email:string,phone:string,message:string,newsletter:bool} $values
 */
function build_contact_mail_text(array $values, string $newsletter): string
{
    $message = $values['message'] !== '' ? $values['message'] : '(Sin mensaje)';
    return "Nueva consulta desde el sitio Pontelog\n\n"
        . "Nombre / Empresa: {$values['name']}\n"
        . "Correo: {$values['email']}\n"
        . "Teléfono: {$values['phone']}\n"
        . "Boletín: {$newsletter}\n\n"
        . "Mensaje:\n{$message}\n";
}

/**
 * Plantilla HTML alineada a la identidad Pontelog (negro, blanco, grises).
 *
 * @param array{name:string,email:string,phone:string,message:string,newsletter:bool} $values
 */
function build_contact_mail_html(array $values, string $newsletter): string
{
    $name = htmlspecialchars($values['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($values['phone'], ENT_QUOTES, 'UTF-8');
    $newsletterSafe = htmlspecialchars($newsletter, ENT_QUOTES, 'UTF-8');
    $rawMessage = $values['message'] !== '' ? $values['message'] : '(Sin mensaje)';
    $message = nl2br(htmlspecialchars($rawMessage, ENT_QUOTES, 'UTF-8'), false);
    $when = htmlspecialchars(date('d/m/Y H:i'), ENT_QUOTES, 'UTF-8') . ' (America/Guayaquil)';
    $mailto = 'mailto:' . rawurlencode($values['email']);

    $row = static function (string $label, string $valueHtml): string {
        return '<tr>'
            . '<td style="padding:14px 0 4px;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;color:#767676;font-family:Arial,Helvetica,sans-serif;">'
            . $label
            . '</td>'
            . '</tr><tr>'
            . '<td style="padding:0 0 16px;font-size:17px;line-height:1.45;color:#111111;font-family:Arial,Helvetica,sans-serif;border-bottom:1px solid #eeeeee;">'
            . $valueHtml
            . '</td>'
            . '</tr>';
    };

    return '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Consulta Pontelog</title>
</head>
<body style="margin:0;padding:0;background:#f3f3f3;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f3f3;padding:28px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;background:#ffffff;border:1px solid #dddddd;">
          <tr>
            <td style="background:#111111;padding:28px 32px;color:#ffffff;font-family:Arial,Helvetica,sans-serif;">
              <div style="font-size:13px;letter-spacing:0.18em;text-transform:uppercase;color:#bbbbbb;">Pontelog</div>
              <div style="margin-top:10px;font-size:26px;line-height:1.25;font-weight:700;color:#ffffff;">Nueva consulta del sitio</div>
              <div style="margin-top:8px;font-size:14px;color:#dddddd;">' . $when . '</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px 32px 8px;font-family:Arial,Helvetica,sans-serif;color:#444444;font-size:16px;line-height:1.5;">
              Llegó una solicitud desde el formulario de contacto. Puedes responder directamente a este correo.
            </td>
          </tr>
          <tr>
            <td style="padding:8px 32px 24px;">
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                ' . $row('Nombre / Empresa', $name) . '
                ' . $row('Correo electrónico', '<a href="' . htmlspecialchars($mailto, ENT_QUOTES, 'UTF-8') . '" style="color:#111111;text-decoration:underline;">' . $email . '</a>') . '
                ' . $row('Teléfono', $phone) . '
                ' . $row('Quiere el boletín', $newsletterSafe) . '
                ' . $row('Mensaje', '<div style="white-space:normal;">' . $message . '</div>') . '
              </table>
            </td>
          </tr>
          <tr>
            <td style="padding:0 32px 32px;">
              <a href="' . htmlspecialchars($mailto, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#111111;color:#ffffff;text-decoration:none;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:700;padding:14px 22px;border-radius:3px;">Responder al contacto</a>
            </td>
          </tr>
          <tr>
            <td style="background:#111111;padding:18px 32px;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#bbbbbb;">
              Mensaje generado automáticamente por el sitio web de Pontelog. No compartas este correo fuera del equipo.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';
}
