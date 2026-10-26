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

/**
 * Miesiąc kalendarza panelu (np. wysuwany kalendarz w stopce layoutu Workspace): siatka pełnych tygodni od poniedziałku,
 * dziś i dni wolne aplikacji (data RRRR-MM-DD → nazwa).
 */
final readonly class CalendarMonth
{
    /**
     * @param array<string, string> $holidays Dni wolne w zakresie siatki: data (RRRR-MM-DD) → nazwa.
     */
    public function __construct(
        public CarbonImmutable $month,
        public CarbonImmutable $today,
        public array $holidays = [],
    ) {}

    public function gridStart(): CarbonImmutable
    {
        return $this->month->startOfMonth()->startOfWeek(CarbonImmutable::MONDAY);
    }

    public function gridEnd(): CarbonImmutable
    {
        return $this->month->endOfMonth()->endOfWeek(CarbonImmutable::SUNDAY)->startOfDay();
    }

    /**
     * Tygodnie siatki (od poniedziałku do niedzieli), także dni sąsiednich miesięcy.
     *
     * @return list<list<CarbonImmutable>>
     */
    public function weeks(): array
    {
        $weeks = [];
        $day = $this->gridStart();

        while ($day <= $this->gridEnd()) {
            $week = [];

            for ($index = 0; $index < 7; $index++) {
                $week[] = $day;
                $day = $day->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    public function isInMonth(CarbonImmutable $day): bool
    {
        return $day->isSameMonth($this->month);
    }

    public function isToday(CarbonImmutable $day): bool
    {
        return $day->isSameDay($this->today);
    }

    public function holiday(CarbonImmutable $day): ?string
    {
        return $this->holidays[$day->toDateString()] ?? null;
    }
}
