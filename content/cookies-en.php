<?php
declare(strict_types=1);

return [
    'title' => 'Cookie policy',
    'updated' => 'November 10, 2026',
    'sections' => [
        [
            'heading' => '1. What cookies are',
            'body' => [
                'Cookies are small files stored on your device to make the site work, remember preferences or measure use. Similar technologies (local storage) are treated the same here.',
            ],
        ],
        [
            'heading' => '2. How we ask for consent',
            'body' => [
                'When you arrive you will see a notice to accept or reject non-essential cookies. Strictly necessary cookies are always used for security and basic operation.',
                'You can change your choice by clearing browser cookies and reloading pontelog.com, or by emailing info@pontelog.com.',
                'This approach follows the informed-consent model used in GDPR/ePrivacy and good practice across Latin America.',
            ],
        ],
        [
            'heading' => '3. Types we use',
            'body' => [
                'Necessary: PHP session cookie for the contact form (CSRF and anti-abuse).',
                'Preferences: we store your banner decision in a cookie or local storage named pontelog_consent.',
                'Marketing/analytics: only if we enable them later and you accept them. Newsletter signup depends on the form checkbox, not the banner.',
            ],
        ],
        [
            'heading' => '4. Orientative table',
            'body' => [
                'pontelog_consent — cookie preference — up to 180 days — first-party.',
                'PHPSESSID (or equivalent) — form security — session — first-party.',
                'Map providers (OpenStreetMap tiles via Leaflet) may log technical network requests when the map loads.',
            ],
        ],
        [
            'heading' => '5. How to manage them',
            'body' => [
                'You can block or delete cookies in your browser settings. Blocking necessary cookies may break parts of the site (such as contact).',
            ],
        ],
        [
            'heading' => 'Notice',
            'body' => [
                'Multi-country template for transparency and consent. Validate it with legal counsel for specific local requirements.',
            ],
        ],
    ],
];
