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

namespace Simplysmart\Mainboard\Holidays;

use RuntimeException;

/**
 * Dostawca świąt nie odpowiedział albo odpowiedział nieoczekiwanie.
 */
final class HolidaysUnavailable extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self("Holidays unavailable: {$reason}");
    }
}
