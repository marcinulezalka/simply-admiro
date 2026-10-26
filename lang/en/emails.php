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

    'layout' => [
        'automatic' => 'This message was sent automatically — please do not reply.',
        'brand_group' => 'The simplysmart product family',
    ],

    'login_code' => [
        'subject' => 'Your sign-in code',
        'greeting' => 'Hello, :name!',
        'body' => 'Your sign-in code: :code. It is valid for :minutes min.',
        'code_label' => 'Your sign-in code:',
        'valid' => 'The code is valid for :minutes min.',
        'ignore' => 'If you are not trying to sign in, ignore this message — nobody can enter your account without the code.',
    ],

    'account_locked' => [
        'subject' => 'Account temporarily locked',
        'greeting' => 'Hello, :name!',
        'body' => 'After several wrong PINs we have locked sign-in until :until.',
        'warning' => 'If this was not you, someone may know your address and have access to your mailbox — change your e-mail password, and your PIN once unlocked.',
    ],

    'security_code' => [
        'recovery_email' => [
            'subject' => 'Confirm your recovery e-mail',
            'code_label' => 'Code to confirm the recovery e-mail:',
            'ignore' => 'If you are not adding this address to your account, ignore this message — without the code it will not be added.',
        ],
        'pin_reset' => [
            'subject' => 'PIN reset',
            'code_label' => 'PIN reset code:',
            'ignore' => 'If you are not resetting your PIN, ignore this message and change your PIN after signing in.',
        ],
    ],

    'pin_changed' => [
        'subject' => 'Your PIN was changed',
        'greeting' => 'Hello, :name!',
        'body' => 'The PIN of your account was changed on :at. We signed out your other sessions and revoked remembered devices.',
        'warning' => 'If this was not you, contact your administrator immediately.',
    ],

    'new_trusted_device' => [
        'subject' => 'New trusted device',
        'greeting' => 'Hello, :name!',
        'body' => 'The device :device was remembered on :at. Signing in on it needs no e-mail code, but still needs your PIN.',
        'manage' => 'If this was not you, revoke it here and change your PIN: :url',
        'manage_html' => 'If this was not you, revoke it and change your PIN.',
        'manage_action' => 'Trusted devices',
    ],

    'device_token_reused' => [
        'subject' => 'Someone used a copy of your trusted device',
        'greeting' => 'Hello, :name!',
        'body' => 'Someone tried to sign in with an outdated trusted device token. To be safe, we signed out all sessions and revoked all remembered devices.',
        'warning' => 'Next time you sign in, we will ask for an e-mail code. If you think someone has access to your browser, change your PIN.',
    ],

];
