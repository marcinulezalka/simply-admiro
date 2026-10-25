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

    'admiro' => [
        'zones' => ['view' => 'Podgląd stref', 'configure' => 'Konfiguracja stref'],
        'accounts' => ['manage' => 'Zarządzanie kontami stref (reset PIN-u, odblokowanie, dezaktywacja)'],
        'organizations' => ['view' => 'Podgląd organizacji i ich członków', 'manage' => 'Zarządzanie organizacjami i członkostwami'],
        'owners' => ['view' => 'Podgląd właścicieli stref', 'assign' => 'Przypisywanie właścicieli stref'],
        'settings' => ['view' => 'Podgląd ustawień systemu', 'manage' => 'Zmiana ustawień systemu'],
        'users' => ['manage' => 'Zarządzanie administratorami'],
        'audit' => ['view' => 'Podgląd dziennika zdarzeń'],
        'heroes' => ['manage' => 'Zarządzanie superadministratorami'],
        'system' => ['maintenance' => 'Konserwacja systemu'],
    ],

];
