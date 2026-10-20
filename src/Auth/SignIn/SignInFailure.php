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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Validation\ValidationException;

/**
 * Jeden ogólny komunikat każdej porażki logowania: nie zdradza, czy konto istnieje,
 * jest nieaktywne, zablokowane, ani który krok zawiódł.
 */
final class SignInFailure
{
    public static function message(): string
    {
        return text('sign_in.failure');
    }

    public static function on(string $field): ValidationException
    {
        return ValidationException::withMessages([$field => self::message()]);
    }
}
