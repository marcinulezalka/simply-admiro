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

use Carbon\CarbonImmutable;

/**
 * Kurs waluty od dostawcy: kod ISO 4217, dzień publikacji, kurs w złotych za 1 jednostkę (tekst dziesiętny)
 * i numer tabeli.
 */
final readonly class ExchangeRateQuote
{
    public function __construct(
        public string $currencyCode,
        public CarbonImmutable $date,
        public string $rate,
        public ?string $tableNumber = null,
    ) {}
}
