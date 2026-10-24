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

use Carbon\CarbonImmutable;

/**
 * Święto od dostawcy: dzień, nazwa w języku kraju i nazwa angielska.
 */
final readonly class HolidayQuote
{
    public function __construct(
        public CarbonImmutable $date,
        public string $localName,
        public string $englishName,
    ) {}
}
