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
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Enums\HolidaySource;
use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Models\Dictionaries\Holiday;

/**
 * Dni wolne wpisywane przez administratora (np. dodatkowy dzień wolny, święto, którego brak w kalendarzu):
 * dodanie na dzień bez wpisu i usunięcie wpisu ręcznego.
 */
final readonly class ManualHolidays
{
    /**
     * @throws HolidaysRejected Gdy brak nazwy albo kraj ma już wpis z tego dnia.
     */
    public function add(Country $country, CarbonImmutable $date, string $name): Holiday
    {
        if (trim($name) === '') {
            throw HolidaysRejected::because('a day off needs a name.');
        }

        if (Holiday::query()->where('country_id', $country->id)->whereDate('holiday_date', $date)->exists()) {
            throw HolidaysRejected::because('the country already has an entry on this day.');
        }

        $holiday = new Holiday();
        $holiday->forceFill([
            ColumnNames::NAME => trim($name),
            ColumnNames::IS_ACTIVE => true,
            'country_id' => $country->id,
            'holiday_date' => $date,
            'source' => HolidaySource::Manual,
            'is_day_off' => true,
        ])->save();

        return $holiday;
    }

    /**
     * @throws HolidaysRejected Gdy wpis pochodzi z kalendarza świąt.
     */
    public function remove(Holiday $holiday): void
    {
        if ($holiday->source !== HolidaySource::Manual) {
            throw HolidaysRejected::because('only manual days off can be removed.');
        }

        $holiday->delete();
    }
}
