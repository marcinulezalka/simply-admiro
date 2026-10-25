<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/admiro.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

return [

    'banner' => [
        'title' => 'This website uses cookies',
        'lead' => 'Necessary cookies keep you signed in and secure. With your consent we also use cookies to remember preferences, analyse traffic and for marketing. You can change your choice at any time in the cookie settings.',
    ],
    'settings' => [
        'title' => 'Cookie settings',
        'lead' => 'Choose which categories of cookies you agree to. Necessary cookies are always on because the site does not work without them.',
        'always_on' => 'Always on',
        'show_cookies' => 'Show cookies',
    ],
    'actions' => [
        'accept' => 'Allow all',
        'reject' => 'Reject',
        'customize' => 'Customise',
        'save' => 'Save preferences',
        'close' => 'Close',
    ],
    'links' => [
        'settings' => 'Cookie settings',
        'policy' => 'Cookie policy',
    ],
    'categories' => [
        'necessary' => ['label' => 'Necessary', 'description' => 'Sign-in, security, remembering your consent and the settings you choose.'],
        'preferences' => ['label' => 'Preferences', 'description' => 'Remembering additional settings such as language or region.'],
        'analytics' => ['label' => 'Analytics', 'description' => 'Anonymous visit statistics that help improve the site.'],
        'marketing' => ['label' => 'Marketing', 'description' => 'Tailoring content and ads, also by partners.'],
    ],
    'decisions' => [
        'accepted' => 'allowed all',
        'rejected' => 'rejected',
        'customized' => 'chose categories',
    ],
    'core' => [
        'session' => 'Application zone session (sign-in, form protection).',
        'device' => 'Trusted device: shorter sign-in without the e-mailed code when you choose it.',
        'consent' => 'Remembering your cookie decision.',
        'appearance' => 'Theme, colour scheme and collapsed menu you choose (browser storage).',
    ],
    'lifetimes' => [
        'session' => 'until the browser is closed or you sign out',
        'device' => 'up to 30 days',
        'consent' => '12 months',
        'storage' => 'until removed in the browser',
    ],
    'policy' => [
        'draft_name' => 'Cookie policy — draft',
        'version_name' => 'Cookie policy, version :version',
        'eyebrow' => 'Version :version of :date',
        'content' => 'Policy',
        'list' => 'Cookies on this site',
        'name' => 'Name',
        'category' => 'Category',
        'purpose' => 'Purpose',
        'lifetime' => 'Retention',
    ],

];
