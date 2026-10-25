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
    | Logowanie w dwóch kartach: formularz po lewej, po prawej ciemna karta marki z logotypem „admiro” na środku.
    | Marka strefy (public/images/, źródła w brand/): logotyp „admiro” w kroju Zen Dots ze złotą koroną nad „i”
    | na logowaniu i w e-mailach, złota korona z dolną kreską jako awatar i ikony strony.
    |
    */

    'zone' => [
        'name' => 'admiro',
        'slug' => env('ADMIRO_SLUG', 'admiro'),
        'model' => AdmiroAccount::class,
        'auth' => [
            'trusted_devices' => ['enabled' => (bool) env('ADMIRO_TRUSTED_DEVICES', false)],
            'pin' => ['min_length' => filter_var(env('ADMIRO_PIN_MIN_LENGTH'), FILTER_VALIDATE_INT, ['options' => ['default' => 8]])],
            'layout' => 'split-reversed',
            'logo' => __DIR__ . '/../public/images/logo.svg',
        ],
        'brand' => [
            'logo' => __DIR__ . '/../public/images/logo.png',
            'signet' => __DIR__ . '/../public/images/signet.png',
        ],
        'favicon' => [
            'ico' => __DIR__ . '/../public/images/favicon.ico',
            'svg' => __DIR__ . '/../public/images/favicon.svg',
            'apple' => __DIR__ . '/../public/images/apple-touch-icon.png',
        ],
    ],

];
