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

namespace Simplysmart\Mainboard\Queries\Dictionaries;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Models\Dictionaries\Holiday;

/**
 * Święta i dni wolne kraju: lista w roku (panel), dni wolne w okresie według daty (kalendarze, grafiki), czy dzień
 * jest roboczy (bez weekendu i dnia wolnego) i dni wolne kraju aplikacji (domyślnego) — bez kraju pusto.
 */
final readonly class HolidaysIn
{
    public function __construct(
        private DefaultEntries $defaults,
    ) {}

    /**
     * @return Collection<int, Holiday>
     */
    public function year(Country $country, int $year): Collection
    {
        $query = Holiday::query();
        $query->where('country_id', $country->id);
        $query->whereYear('holiday_date', $year);
        $query->orderBy('holiday_date');

        return $query->get();
    }

    /**
     * @return array<string, Holiday> Data (RRRR-MM-DD) → dzień wolny.
     */
    public function daysOff(?Country $country, CarbonImmutable $from, CarbonImmutable $to): array
    {
        if ($country === null) {
            return [];
        }

        $query = Holiday::query();
        $query->where('country_id', $country->id);
        $query->where('is_day_off', true);
        $query->whereDate('holiday_date', '>=', $from);
        $query->whereDate('holiday_date', '<=', $to);
        $days = [];

        foreach ($query->get() as $holiday) {
            $days[$holiday->holiday_date->toDateString()] = $holiday;
        }

        return $days;
    }

    /**
     * Dni wolne kraju aplikacji (`DefaultEntries::country()`) w okresie.
     *
     * @return array<string, Holiday>
     */
    public function applicationDaysOff(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->daysOff($this->defaults->country(), $from, $to);
    }

    public function isBusinessDay(?Country $country, CarbonImmutable $date): bool
    {
        return ! $date->isWeekend() && $this->daysOff($country, $date, $date) === [];
    }

    public function find(Country $country, string $token): ?Holiday
    {
        return Holiday::query()->where('country_id', $country->id)->where('token', $token)->first();
    }
}
