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

namespace Simplysmart\Admiro\View\Calendar;

use Carbon\CarbonImmutable;
use Simplysmart\Admiro\Queries\Dictionaries\HolidaysIn;

/**
 * Buduje miesiąc kalendarza panelu z dniami wolnymi kraju aplikacji (Queries\Dictionaries\HolidaysIn) w zakresie siatki.
 */
final readonly class CalendarMonths
{
    public function __construct(
        private HolidaysIn $holidays,
    ) {}

    public function current(): CalendarMonth
    {
        $today = CarbonImmutable::today();
        $month = new CalendarMonth($today->startOfMonth(), $today);
        $names = [];

        foreach ($this->holidays->applicationDaysOff($month->gridStart(), $month->gridEnd()) as $date => $holiday) {
            $names[$date] = $holiday->translatedName();
        }

        return new CalendarMonth($month->month, $today, $names);
    }
}
