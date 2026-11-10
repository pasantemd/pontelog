<?php
declare(strict_types=1);

return [
    'title' => 'Cookie policy',
    'updated' => 'November 10, 2026',
    'sections' => [
        [
            'heading' => '1. Cookies this site uses',
            'body' => [
                'Site session (PHP session cookie): used for the contact form (protection against forged submissions and resend limits). It lasts for the browser session or according to server settings.',
                'Language (pontelog_lang cookie): stores the language chosen in the menu (Spanish, English, Portuguese or Chinese). Duration: up to 1 year.',
                'Notice acknowledgment (pontelog_consent, in a cookie and/or local storage): stores that you saw this site’s cookie notice. Duration: up to 180 days.',
            ],
        ],
        [
            'heading' => '2. Contact map',
            'body' => [
                'When the contact-section map is shown, the browser requests map images from OpenStreetMap. That service may keep technical logs (for example IP address and requested resource). These are not cookies created by Pontelog.',
            ],
        ],
        [
            'heading' => '3. What this site does not do',
            'body' => [
                'We do not install audience-measurement, advertising or social-network cookies.',
                'The newsletter is handled only if you check the box on the contact form; it is not turned on by the cookie notice.',
            ],
        ],
        [
            'heading' => '4. How to manage them',
            'body' => [
                'You can delete cookies and local storage in your browser settings.',
                'If you delete the session cookie, you may need to reload the page to use the form again.',
                'Questions: info@pontelog.com.',
            ],
        ],
    ],
];
