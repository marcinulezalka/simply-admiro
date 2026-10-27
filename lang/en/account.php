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

    'organization' => 'Organization',
    'role' => 'Role',
    'settings_group' => 'Account',
    'appearance_title' => 'Appearance',
    'theme_title' => 'Theme',
    'theme_hint' => 'Light, dark or high contrast. This browser remembers your choice.',
    'palette_hint' => 'The accent colour of the application. This browser remembers your choice.',
    'organization_switch' => 'Switch',
    'menu' => 'Account',
    'pin_title' => 'Change PIN',
    'pin_intro' => 'Send a code to your e-mail address, then enter your current PIN, the code and a new PIN (:min to :max digits).',
    'send_code' => 'Send code',
    'recovery' => [
        'title' => 'Recovery e-mail',
        'intro' => 'If you forget your PIN, a code sent to this address lets you remove it without an administrator. Signing in still needs your account address.',
        'current' => 'Confirmed recovery address',
        'email' => 'Recovery e-mail',
        'new_email' => 'New recovery e-mail',
        'same_as_primary' => 'The recovery address must differ from the account address.',
        'code_sent' => 'We have sent a code to the recovery address. Enter it to confirm the address.',
        'code_hint' => 'Code sent to :email.',
        'confirm' => 'Confirm address',
        'saved' => 'Recovery e-mail saved.',
        'removed' => 'Recovery e-mail removed.',
    ],
    'code_sent' => 'We have sent a code to the account address.',
    'current_pin' => 'Current PIN',
    'new_pin' => 'New PIN',
    'pin_invalid' => 'The current PIN is incorrect.',
    'code_invalid' => 'The code is incorrect or has expired. Send a new one.',
    'pin_same' => 'The new PIN must differ from the current one.',
    'pin_changed' => 'PIN changed. Your other sessions were signed out and remembered devices revoked.',
    'devices_title' => 'Trusted devices',
    'devices_empty' => 'You have no remembered devices.',
    'device' => 'Device',
    'device_last_used' => 'Last used',
    'device_expires' => 'Valid until',
    'this_device' => 'this device',
    'device_revoke' => 'Revoke',
    'devices_revoke_all' => 'Revoke all',
    'devices_revoked' => 'The device is no longer trusted: signing in on it requires an e-mail code again.',
    'profile_title' => 'My profile',
    'avatar_color' => 'Avatar color',
    'profile_saved' => 'Profile saved.',
    'profile_details' => 'Account details',
    'name' => 'Full name',
    'email' => 'E-mail address',
    'email_hint' => 'Your e-mail address is your sign-in — an administrator changes it.',
    'language' => 'Interface language',
    'language_auto' => 'As in the page address',
    'language_hint' => 'After saving and at every sign-in the application opens in this language.',
    'organizations_title' => 'My organizations',
    'organization_current' => 'current',
    'organization_no_roles' => 'no roles',
    'security_title' => 'Security',
    'devices_hint' => 'On a trusted device you sign in with your PIN only. Revoke devices you no longer use.',
    'sign_out_everywhere' => 'Sign out of all devices',
    'sign_out_everywhere_hint' => 'Ends every session of this account, including this one, and revokes trusted devices. The next sign-in will need an e-mailed code.',
    'signed_out_everywhere' => 'Signed out of all devices.',
    'history' => [
        'title' => 'Recent account events',
        'lead' => 'If you do not recognize something, change your PIN and sign out of all devices.',
        'empty' => 'No events.',
        'when' => 'When',
        'event' => 'Event',
        'detail' => 'Details',
        'devices_count' => 'Devices: :count',
    ],
    'avatar_colors' => [
        'slate' => 'Graphite',
        'red' => 'Red',
        'amber' => 'Amber',
        'green' => 'Green',
        'teal' => 'Teal',
        'blue' => 'Blue',
        'violet' => 'Violet',
        'pink' => 'Pink',
    ],

];
