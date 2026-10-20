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

];
