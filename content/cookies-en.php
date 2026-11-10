<?php
declare(strict_types=1);

return [
    'title' => 'Cookie policy',
    'updated' => 'November 10, 2026',
    'sections' => [
        [
            'heading' => '1. What this policy covers',
            'body' => [
                'This policy explains the cookies and local storage used by pontelog.com. It only describes what the site does today.',
            ],
        ],
        [
            'heading' => '2. Cookies and technologies we use',
            'body' => [
                'Necessary (PHP session): enable the secure contact form (CSRF protection and resend limits). They expire when the browser session ends or according to server settings.',
                'Language preference (pontelog_lang cookie): remembers the language you chose (Spanish, English, Portuguese or Chinese). Approximate duration: 1 year.',
                'Cookie-notice preference (pontelog_consent in a cookie and/or local storage): stores whether you accepted or limited non-essential cookies. Approximate duration: 180 days.',
            ],
        ],
        [
            'heading' => '3. What we do not use today',
            'body' => [
                'This site does not install web-analytics cookies, advertising network cookies or third-party tracking pixels for audience measurement or behavioral advertising.',
                'Newsletter subscription does not depend on the cookie banner: it happens only if you check the box on the contact form.',
            ],
        ],
        [
            'heading' => '4. Third-party content when viewing the map',
            'body' => [
                'If you load the contact section with the map, the browser requests map tiles from OpenStreetMap. That provider may create technical logs (for example IP and requested resource). These are not Pontelog cookies.',
            ],
        ],
        [
            'heading' => '5. Consent and management',
            'body' => [
                'Necessary cookies are used for the site to work. Preference cookies are stored when you use the language switcher or the cookie notice.',
                'You can delete cookies and local storage in your browser. If you remove necessary cookies, the contact form may stop submitting correctly.',
                'Privacy questions: info@pontelog.com.',
            ],
        ],
    ],
];
