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
        'subject' => 'Your sign-in code',
        'greeting' => 'Hello, :name!',
        'body' => 'Your sign-in code: :code. It is valid for :minutes min.',
        'ignore' => 'If you are not trying to sign in, ignore this message — nobody can enter your account without the code.',
    ],

    'account_locked' => [
        'subject' => 'Account temporarily locked',
        'greeting' => 'Hello, :name!',
        'body' => 'After several wrong PINs we have locked sign-in until :until.',
        'warning' => 'If this was not you, someone may know your address and have access to your mailbox — change your e-mail password, and your PIN once unlocked.',
    ],

];
