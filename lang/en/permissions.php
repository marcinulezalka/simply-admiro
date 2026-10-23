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

    'admiro' => [
        'zones' => ['view' => 'View zones', 'configure' => 'Configure zones'],
        'accounts' => ['manage' => 'Manage zone accounts (PIN reset, unlock, deactivation)'],
        'owners' => ['view' => 'View zone owners', 'assign' => 'Assign zone owners'],
        'settings' => ['view' => 'View system settings', 'manage' => 'Change system settings'],
        'users' => ['manage' => 'Manage administrators'],
        'audit' => ['view' => 'View the audit log'],
        'heroes' => ['manage' => 'Manage super administrators'],
        'system' => ['maintenance' => 'System maintenance'],
    ],

];
