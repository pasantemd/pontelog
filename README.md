# Pontelog en PHP

Web de Pontelog (`pontelog.com`) con ocho tarjetas giratorias, contacto con mapa y dos formularios de campaña. PHP nativo, sin WordPress ni base de datos. El envío de correo usa PHPMailer (incluido en `lib/PHPMailer/`).

## Ejecutar

Sitio en producción: **https://pontelog.com/**

Requiere PHP 8.1 o superior con DOM, OpenSSL, mod_rewrite y AllowOverride.

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

Las consultas se guardan fuera de la carpeta pública del sitio (por defecto, un directorio hermano `pontelog-data/consultas.jsonl`). Se aplican CSRF, validación, honeypot y límite de frecuencia. El consentimiento al boletín se registra sin suscripción automática.

Para enviar el aviso por correo con PHPMailer en `pontelog.com`:

1. Copia `config.local.php.example` a `config.local.php` en la raíz del sitio.
2. Completa host, puerto, usuario y contraseña SMTP del hosting (suele ser `mail.pontelog.com`).
3. Ajusta `data_dir` a una ruta privada fuera del document root.

Ejemplo mínimo en `config.local.php`:

```php
<?php
return [
    'mail_enabled' => true,
    'contact_email' => 'info@pontelog.com',
    'mail_copy' => 'copia@pontelog.com',
    'mail_from' => 'info@pontelog.com',
    'mail_from_name' => 'Pontelog',
    'smtp_host' => 'mail.pontelog.com',
    'smtp_port' => 587,
    'smtp_user' => 'info@pontelog.com',
    'smtp_pass' => 'TU_CLAVE_SMTP',
    'smtp_secure' => 'tls',
    'data_dir' => '/home/TU_USUARIO/pontelog-data',
];
```

`contact_email` recibe el correo; `mail_copy` manda una copia visible (CC) a uno o varios correos separados por coma.

También puedes usar variables de entorno (`PONTELOG_MAIL_ENABLED`, `PONTELOG_SMTP_HOST`, `PONTELOG_SMTP_PORT`, `PONTELOG_SMTP_USER`, `PONTELOG_SMTP_PASS`, `PONTELOG_SMTP_SECURE`, `PONTELOG_CONTACT_EMAIL`, `PONTELOG_MAIL_COPY`, `PONTELOG_MAIL_FROM`, `PONTELOG_DATA_DIR`). Las variables de entorno tienen prioridad sobre `config.local.php`.

Si el SMTP no está configurado o falla, la consulta igual se guarda en disco y el mensaje al visitante indica contacto directo a `info@pontelog.com`.

### Boletín con Mautic

Si el visitante marca el boletín, el sitio:

1. Usa la **API OAuth2** de Mautic (`/oauth/v2/token` + `/api/contacts/new`) y agrega el contacto al segmento **20** (`Boletin Pontelog Web`).
2. Si la API no está configurada, hace POST al formulario público **ID 14** (`boletinpontelogweb`).

En `config.local.php`:

```php
'mautic_enabled' => true,
'mautic_base_url' => 'https://mkt.apracom-ec.com',
'mautic_client_id' => 'TU_PUBLIC_KEY',
'mautic_client_secret' => 'TU_SECRET',
'mautic_segment_id' => '20',
'mautic_form_id' => '14',
'mautic_form_name' => 'boletinpontelogweb',
'mautic_email_field' => 'email',
'mautic_name_field' => 'nombre',
'mautic_phone_field' => 'telefono',
```

No subas las claves al repositorio. Errores: `pontelog-data/mautic-errors.log`.

### Idiomas

El sitio está en **español, inglés, portugués y chino**.

Detección (en este orden):

1. `?lang=es|en|pt|zh` (selector manual en la cabecera; se guarda en cookie `pontelog_lang`)
2. Cookie guardada
3. Cabecera `Accept-Language` del navegador (idioma/región preferidos del usuario)
4. Español por defecto

No usamos geolocalización por IP: el idioma del navegador refleja mejor cómo habla la persona y evita VPN/viajes incorrectos. El usuario siempre puede cambiar el idioma a mano.

### Privacidad y cookies

Rutas:

- `/politica-de-privacidad/`
- `/politica-de-cookies/`

Hay un banner de consentimiento (aceptar / solo necesarias). El texto es una plantilla multi-país alineada a principios GDPR/LGPD/LOPDP; conviene validarlo con asesoría legal.

Los formularios de campaña y webinar mantienen el destino HTTPS original en `mkt.apracom-ec.com`. Las cantidades aparecen al seleccionar una placa y «Describa» al elegir «Otros». Las fechas de las campañas se conservan como fueron publicadas; no representan disponibilidad actual verificada.

El mapa de contacto es propio (GeoJSON de América + Leaflet local): países en gris, puntos negros y nombre al pasar el cursor. No usa teselas de OpenStreetMap.
