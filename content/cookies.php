<?php
declare(strict_types=1);

return [
    'title' => 'Política de cookies',
    'updated' => '10 de noviembre de 2026',
    'sections' => [
        [
            'heading' => '1. Qué son las cookies',
            'body' => [
                'Las cookies son pequeños archivos que el sitio guarda en tu dispositivo para hacerlo funcionar, recordar preferencias o medir uso. Tecnologías similares (almacenamiento local) se tratan igual en esta política.',
            ],
        ],
        [
            'heading' => '2. Cómo pedimos tu consentimiento',
            'body' => [
                'Al entrar verás un aviso para aceptar o rechazar cookies no esenciales. Las cookies estrictamente necesarias se usan siempre para seguridad y funcionamiento básico.',
                'Puedes cambiar tu elección borrando cookies del navegador y recargando pontelog.com, o escribiendo a info@pontelog.com.',
                'Este enfoque sigue el modelo de consentimiento informado usado en GDPR/ePrivacy y es compatible con buenas prácticas en Latinoamérica.',
            ],
        ],
        [
            'heading' => '3. Tipos que usamos',
            'body' => [
                'Necesarias: cookie de sesión PHP para el formulario de contacto (CSRF y anti-abuso). Sin ellas el envío seguro no funciona.',
                'Preferencias: guardamos tu decisión del banner (aceptar/rechazar) en una cookie o almacenamiento local llamada pontelog_consent.',
                'Marketing / analítica: solo si las activamos en el futuro y tú las aceptas. Hoy el boletín se suscribe solo si marcas la casilla del formulario; eso no depende del banner.',
            ],
        ],
        [
            'heading' => '4. Tabla orientativa',
            'body' => [
                'pontelog_consent — preferencia de cookies — hasta 180 días — propia.',
                'PHPSESSID (o equivalente de sesión) — seguridad del formulario — sesión — propia.',
                'Proveedores de mapa (teselas OpenStreetMap vía Leaflet) pueden registrar peticiones técnicas de red al cargar el mapa; no son cookies de Pontelog, pero implican conexión a terceros.',
            ],
        ],
        [
            'heading' => '5. Cómo gestionarlas',
            'body' => [
                'Puedes bloquear o borrar cookies desde la configuración de tu navegador. Si bloqueas las necesarias, partes del sitio (como el contacto) pueden dejar de funcionar.',
            ],
        ],
        [
            'heading' => 'Aviso',
            'body' => [
                'Plantilla multi-país para transparencia y consentimiento. Valídala con asesoría legal si operas con requisitos locales específicos.',
            ],
        ],
    ],
];
