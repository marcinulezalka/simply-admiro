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

namespace Simplysmart\Mainboard\Auth;

/**
 * Nazwy kolumn tabeli kont strefy; schemat, model i zapis stanu używają tych stałych.
 */
final class AccountColumns
{
    public const string TOKEN = 'token';

    public const string NAME = 'name';

    public const string EMAIL = 'email';

    public const string EMAIL_HASH = 'email_hash';

    public const string PIN_HASH = 'pin_hash';

    public const string PIN_SET_AT = 'pin_set_at';

    public const string PIN_FAILED_ATTEMPTS = 'pin_failed_attempts';

    public const string LOCK_LEVEL = 'lock_level';

    public const string LOCKED_UNTIL = 'locked_until';

    public const string AUTH_EPOCH = 'auth_epoch';

    public const string IS_ACTIVE = 'is_active';

    public const string LAST_LOGIN_AT = 'last_login_at';

    public const string LAST_LOGIN_IP = 'last_login_ip';
}
