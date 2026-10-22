# Pontelog en PHP

Web de Pontelog con ocho tarjetas giratorias, contacto con mapa y dos formularios de campaña. PHP nativo, sin WordPress, Composer ni base de datos.

## Ejecutar

Con Apache de XAMPP: **http://localhost/pontelog/**.

Requiere PHP 8.1 o superior con DOM, mod_rewrite y AllowOverride. La ejecución se realiza mediante Apache de XAMPP.

Rutas adicionales conservadas:

- `/invitacion-de-webinar-2026/`
- `/formulario-pontelog/`

## Archivos

- `index.php` e `includes/`: plantillas, formulario y validación PHP.
- `content/site.php`: identificadores, textos y logotipos de las soluciones, empresa, misión y visión.
- `content/form-3.html` y `content/form-13.html`: campos originales de campaña y webinar.
- `assets/site.css`, `assets/forms.css` y `assets/site.js`: presentación e interacciones.
- `assets/images/`, `assets/fonts/` y `assets/vendor/leaflet/`: recursos locales utilizados.
- `assets/map-markers.json`: coordenadas del mapa original.

## Contacto

Las consultas se guardan fuera de la carpeta pública, por defecto en `C:/xampp/pontelog-data/consultas.jsonl`. Se aplican CSRF, validación, honeypot y límite de frecuencia. El consentimiento al boletín se registra sin suscripción automática.

Para notificaciones por correo, configurar estas variables y un transporte operativo para `mail()` en PHP:

```text
PONTELOG_MAIL_ENABLED=1
PONTELOG_CONTACT_EMAIL=info@pontelog.com
PONTELOG_DATA_DIR=C:/xampp/pontelog-data
```

El mensaje confirma guardado cuando el correo no está habilitado.

Los formularios de campaña y webinar mantienen el destino HTTPS original en `mkt.apracom-ec.com`. Las cantidades aparecen al seleccionar una placa y «Describa» al elegir «Otros». Las fechas de las campañas se conservan como fueron publicadas; no representan disponibilidad actual verificada.

Leaflet se sirve localmente; las teselas de OpenStreetMap necesitan Internet. El mapa está visible junto al formulario y conserva una lista alternativa de países, sin afirmar oficinas ni cobertura comercial.
