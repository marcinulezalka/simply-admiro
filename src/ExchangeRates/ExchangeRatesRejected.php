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

namespace Simplysmart\Admiro\ExchangeRates;

use RuntimeException;

/**
 * Zmiana kursów odrzucona: kurs niedodatni, kurs z tego dnia już jest albo usuwany kurs pochodzi z NBP.
 */
final class ExchangeRatesRejected extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self("Exchange rate change rejected: {$reason}");
    }
}
