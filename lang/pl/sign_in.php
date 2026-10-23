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

    'title' => 'Logowanie',
    'email' => 'Adres e-mail',
    'continue' => 'Dalej',
    'code_title' => 'Wpisz kod z e-maila',
    'code_intro' => 'Jeśli konto o adresie :email istnieje, wysłaliśmy na nie :length-cyfrowy kod. Jest ważny przez :minutes min.',
    'code' => 'Kod',
    'resend' => 'Wyślij kod ponownie',
    'resend_wait' => 'Nowy kod możesz wysłać za :seconds s.',
    'code_resent' => 'Jeśli konto istnieje, wysłaliśmy nowy kod.',
    'change_email' => 'Zmień adres',
    'pin_title' => 'Wpisz PIN',
    'pin' => 'PIN',
    'pin_setup_title' => 'Ustaw PIN',
    'pin_setup_intro' => 'PIN ma od :min do :max cyfr. Unikaj powtórzeń i kolejnych cyfr.',
    'pin_confirmation' => 'Powtórz PIN',
    'sign_out' => 'Wyloguj',
    'signed_in_as' => 'Zalogowano jako :name.',
    'failure' => 'Nie udało się zalogować. Sprawdź dane i spróbuj ponownie.',
    'expired' => 'Sesja logowania wygasła. Zacznij od nowa.',
    'page_expired' => 'Strona wygasła. Zaloguj się ponownie.',
    'too_many_attempts' => 'Zbyt wiele prób. Spróbuj ponownie za :seconds s.',
    'weak_pin' => 'Ten PIN jest zbyt prosty — unikaj powtórzeń i kolejnych cyfr.',
    'remember_device' => 'Zapamiętaj to urządzenie na :days dni (następnym razem bez kodu, PIN nadal wymagany)',
    'honeypot_field' => 'Strona internetowa',
    'forgot_pin_link' => 'Nie pamiętam PIN-u',
    'signed_out' => 'Wylogowano. Do zobaczenia!',

    'forgot_pin' => [
        'title' => 'Nie pamiętasz PIN-u?',
        'lead' => 'PIN-u nie da się odzyskać — nie zna go nikt, także administrator. Można go tylko ustawić od nowa.',
        'steps_title' => 'Jak ustawić nowy PIN',
        'steps' => [
            'admin' => 'Poproś administratora tej części aplikacji o reset PIN-u.',
            'revoked' => 'Reset wyloguje Cię ze wszystkich urządzeń i odwoła zaufane urządzenia.',
            'sign_in' => 'Zaloguj się adresem e-mail i kodem, który do Ciebie wyślemy.',
            'set' => 'Ustaw nowy PIN i powtórz go, żeby potwierdzić.',
        ],
        'back' => 'Wróć do logowania',
    ],

];
