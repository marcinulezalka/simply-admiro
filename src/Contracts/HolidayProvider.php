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

namespace Simplysmart\Admiro\Contracts;

use Simplysmart\Admiro\Holidays\HolidayQuote;
use Simplysmart\Admiro\Holidays\HolidaysUnavailable;

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
