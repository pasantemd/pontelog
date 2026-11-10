# Pontelog en PHP

Web de Pontelog con ocho tarjetas giratorias, contacto con mapa y dos formularios de campaña. PHP nativo, sin WordPress ni base de datos. El envío de correo usa PHPMailer (incluido en `lib/PHPMailer/`).

## Ejecutar

Con Apache de XAMPP: **http://localhost/pontelog/**.

Requiere PHP 8.1 o superior con DOM, OpenSSL, mod_rewrite y AllowOverride. La ejecución se realiza mediante Apache de XAMPP.

Rutas adicionales conservadas:

- `/invitacion-de-webinar-2026/`
- `/formulario-pontelog/`

## Archivos

- `index.php` e `includes/`: plantillas, formulario, validación y envío SMTP.
- `config.local.php.example`: plantilla de SMTP; cópiala a `config.local.php`.
- `lib/PHPMailer/`: PHPMailer 6.9.3 embebido (sin Composer).
- `content/site.php`: identificadores, textos y logotipos de las soluciones, empresa, misión y visión.
- `content/form-3.html` y `content/form-13.html`: campos originales de campaña y webinar.
- `assets/site.css`, `assets/forms.css` y `assets/site.js`: presentación e interacciones.
- `assets/images/`, `assets/fonts/` y `assets/vendor/leaflet/`: recursos locales utilizados.
- `assets/map-markers.json`: coordenadas del mapa original.

## Contacto y correo

Las consultas se guardan fuera de la carpeta pública, por defecto en `C:/xampp/pontelog-data/consultas.jsonl`. Se aplican CSRF, validación, honeypot y límite de frecuencia. El consentimiento al boletín se registra sin suscripción automática.

Para enviar el aviso por correo con PHPMailer:

1. Copia `config.local.php.example` a `config.local.php`.
2. Completa host, puerto, usuario y contraseña SMTP.
3. Reinicia Apache si hace falta.

Ejemplo mínimo en `config.local.php`:

```php
<?php
return [
    'mail_enabled' => true,
    'contact_email' => 'info@pontelog.com',
    'mail_from' => 'info@pontelog.com',
    'mail_from_name' => 'Pontelog',
    'smtp_host' => 'smtp.tudominio.com',
    'smtp_port' => 587,
    'smtp_user' => 'info@pontelog.com',
    'smtp_pass' => 'TU_CLAVE_SMTP',
    'smtp_secure' => 'tls',
    'data_dir' => 'C:/xampp/pontelog-data',
];
```

También puedes usar variables de entorno (`PONTELOG_MAIL_ENABLED`, `PONTELOG_SMTP_HOST`, `PONTELOG_SMTP_PORT`, `PONTELOG_SMTP_USER`, `PONTELOG_SMTP_PASS`, `PONTELOG_SMTP_SECURE`, `PONTELOG_CONTACT_EMAIL`, `PONTELOG_MAIL_FROM`, `PONTELOG_DATA_DIR`). Las variables de entorno tienen prioridad sobre `config.local.php`.

Si el SMTP no está configurado o falla, la consulta igual se guarda en disco y el mensaje al visitante indica contacto directo a `info@pontelog.com`.

Los formularios de campaña y webinar mantienen el destino HTTPS original en `mkt.apracom-ec.com`. Las cantidades aparecen al seleccionar una placa y «Describa» al elegir «Otros». Las fechas de las campañas se conservan como fueron publicadas; no representan disponibilidad actual verificada.

Leaflet se sirve localmente; las teselas de OpenStreetMap necesitan Internet. El mapa está visible junto al formulario y conserva una lista alternativa de países, sin afirmar oficinas ni cobertura comercial.
