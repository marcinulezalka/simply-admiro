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

use Simplysmart\Admiro\AdmiroServiceProvider;
use Simplysmart\Admiro\Models\AdmiroAccount;

// Fonty logowania (latin i latin-ext z polskimi literami; licencja SIL OFL w public/fonts/).
$latin = 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD';
$latinExt = 'U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF';

$fonts = [];

foreach ([['space-grotesk', 'Space Grotesk', 600, 'normal'], ['instrument-serif', 'Instrument Serif', 400, 'italic']] as [$file, $family, $weight, $style]) {
    foreach (['latin' => $latin, 'latin-ext' => $latinExt] as $subset => $range) {
        $fonts["{$file}-{$subset}-{$weight}-{$style}"] = [
            'family' => $family,
            'weight' => $weight,
            'style' => $style,
            'file' => __DIR__ . "/../public/fonts/{$file}-{$subset}-{$weight}-{$style}.woff2",
            'unicode_range' => $range,
        ];
    }
}

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
    | Tytuł logowania z tekstów paczki (lang/<locale>/sign_in.php) w kroju Space Grotesk, wyróżnione słowo
    | kursywą Instrument Serif (fonty w public/fonts/, przypisane tokenom w public/css/admiro.css).
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
        'stylesheets' => [__DIR__ . '/../public/css/admiro.css'],
        'fonts' => $fonts,
        'legal_documents' => ['privacy', 'gdpr'],
        'texts' => AdmiroServiceProvider::NAMESPACE,
        'favicon' => [
            'ico' => __DIR__ . '/../public/images/favicon.ico',
            'svg' => __DIR__ . '/../public/images/favicon.svg',
            'apple' => __DIR__ . '/../public/images/apple-touch-icon.png',
        ],
    ],

];
