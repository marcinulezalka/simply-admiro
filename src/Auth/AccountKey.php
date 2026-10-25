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

namespace Simplysmart\Admiro\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use LogicException;

/**
 * Identyfikator konta strefy (`int|string`): jedno miejsce sprawdzania typu klucza konta.
 */
final class AccountKey
{
    /**
     * @throws LogicException Gdy konto nie ma identyfikatora typu int lub string.
     */
    public static function of(Authenticatable $account): int|string
    {
        return self::from($account->getAuthIdentifier()) ?? throw new LogicException('Account identifier must be an integer or a string.');
    }

    /**
     * Identyfikator z dowolnej wartości (np. `Guard::id()`); null, gdy to nie int ani string.
     */
    public static function from(mixed $id): int|string|null
    {
        return is_int($id) || is_string($id) ? $id : null;
    }
}
