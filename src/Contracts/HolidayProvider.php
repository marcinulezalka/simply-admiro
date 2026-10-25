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

namespace Simplysmart\Mainboard\Contracts;

use Simplysmart\Mainboard\Holidays\HolidayQuote;
use Simplysmart\Mainboard\Holidays\HolidaysUnavailable;

/**
 * Źródło ogólnokrajowych świąt — dni wolnych od pracy (domyślnie Nager.Date, `Holidays\NagerDate`): święta kraju
 * (ISO 3166-1 alpha-2) w roku; kraj nieobsługiwany to pusta lista.
 */
interface HolidayProvider
{
    /**
     * @return list<HolidayQuote>
     *
     * @throws HolidaysUnavailable
     */
    public function forYear(string $countryCode, int $year): array;
}
