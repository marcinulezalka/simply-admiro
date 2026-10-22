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

    'login_code' => [
        'subject' => 'Twój kod logowania',
        'greeting' => 'Dzień dobry, :name!',
        'body' => 'Twój kod logowania: :code. Jest ważny przez :minutes min.',
        'ignore' => 'Jeśli to nie Ty próbujesz się zalogować, zignoruj tę wiadomość — bez kodu nikt nie wejdzie na Twoje konto.',
    ],

    'account_locked' => [
        'subject' => 'Konto tymczasowo zablokowane',
        'greeting' => 'Dzień dobry, :name!',
        'body' => 'Po kilku błędnych PIN-ach zablokowaliśmy logowanie do :until.',
        'warning' => 'Jeśli to nie Ty, ktoś może znać Twój adres i dostęp do skrzynki — zmień hasło do poczty, a po odblokowaniu PIN.',
    ],

    'pin_changed' => [
        'subject' => 'PIN został zmieniony',
        'greeting' => 'Dzień dobry, :name!',
        'body' => 'PIN do Twojego konta zmieniono :at. Pozostałe sesje wylogowaliśmy, a zapamiętane urządzenia odwołaliśmy.',
        'warning' => 'Jeśli to nie Ty, natychmiast skontaktuj się z administratorem.',
    ],

    'new_trusted_device' => [
        'subject' => 'Nowe zaufane urządzenie',
        'greeting' => 'Dzień dobry, :name!',
        'body' => 'Urządzenie :device zostało zapamiętane :at. Logowanie na nim nie wymaga kodu z e-maila, ale nadal wymaga PIN-u.',
        'manage' => 'Jeśli to nie Ty, odwołaj je tutaj i zmień PIN: :url',
    ],

    'device_token_reused' => [
        'subject' => 'Ktoś użył kopii Twojego zaufanego urządzenia',
        'greeting' => 'Dzień dobry, :name!',
        'body' => 'Ktoś próbował zalogować się nieaktualnym tokenem zaufanego urządzenia. Dla bezpieczeństwa wylogowaliśmy wszystkie sesje i odwołaliśmy wszystkie zapamiętane urządzenia.',
        'warning' => 'Przy następnym logowaniu poprosimy o kod z e-maila. Jeśli podejrzewasz, że ktoś ma dostęp do Twojej przeglądarki, zmień PIN.',
    ],

];
