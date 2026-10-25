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
 * Zmiana dni wolnych odrzucona: dzień kraju ma już wpis, brak nazwy albo usuwany wpis nie jest ręczny.
 */
final class HolidaysRejected extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self("Holiday change rejected: {$reason}");
    }
}
