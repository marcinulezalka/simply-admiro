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

use Simplysmart\Admiro\Models\AdmiroAccount;

return [

    /*
    |--------------------------------------------------------------------------
    | Strefa administratorów
    |--------------------------------------------------------------------------
    |
    | Definicja strefy dla Core (docs/strefy.md w simplysmart/mainboard). Slug to tylko segment URL:
    | jego zmiana nie zmienia nazw tras (prefiks "admiro." jest stały) i nie może być kodem języka.
    | Administratorzy domyślnie zawsze logują się kodem z e-maila i PIN-em (bez zaufanych urządzeń).
    | PIN administratorów ma co najmniej 8 cyfr zamiast 4 z mainboard (ADMIRO_PIN_MIN_LENGTH); maksimum 16 z mainboard.
    |
    */

    'zone' => [
        'name' => 'admiro',
        'slug' => env('ADMIRO_SLUG', 'admiro'),
        'model' => AdmiroAccount::class,
        'auth' => [
            'trusted_devices' => ['enabled' => (bool) env('ADMIRO_TRUSTED_DEVICES', false)],
            'pin' => ['min_length' => filter_var(env('ADMIRO_PIN_MIN_LENGTH'), FILTER_VALIDATE_INT, ['options' => ['default' => 8]])],
        ],
    ],

];
