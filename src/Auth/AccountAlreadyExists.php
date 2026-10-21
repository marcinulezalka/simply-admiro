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

use RuntimeException;

/**
 * Strefa ma już konto z tym adresem e-mail.
 */
final class AccountAlreadyExists extends RuntimeException
{
    public static function inZone(string $zone): self
    {
        return new self("Zone [{$zone}] already has an account with this e-mail address.");
    }
}
