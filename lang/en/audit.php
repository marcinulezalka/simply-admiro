<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

return [

    'events' => [
        'sign_in' => 'Successful sign-in',
        'pin_rejected' => 'Wrong PIN',
        'account_locked' => 'Account locked',
        'sign_out' => 'Sign-out',
        'access_revoked' => 'Sessions ended',
        'pin_changed' => 'PIN changed',
        'recovery_email_set' => 'Recovery e-mail added',
        'recovery_email_removed' => 'Recovery e-mail removed',
        'trusted_device_registered' => 'New trusted device',
        'trusted_devices_revoked' => 'Devices revoked',
        'role_assigned' => 'Role assigned',
        'role_revoked' => 'Role revoked',
        'record_accessed' => 'Record opened',
    ],
    'all_events' => 'All events',
    'sensitive' => 'sensitive data',
    'operator' => 'System operator',
    'system' => 'system',

];
