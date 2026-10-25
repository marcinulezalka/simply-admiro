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

namespace Simplysmart\Admiro\Console;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Holidays\HolidaySync;
use Simplysmart\Admiro\Holidays\HolidaysUnavailable;
use Simplysmart\Admiro\Models\Dictionaries\Country;

/**
 * Pobiera święta (dni wolne od pracy) dla aktywnych krajów słownika (albo --country) na bieżący i następny rok
 * (albo --year). Idempotentna; uruchamiana przez scheduler raz w miesiącu.
 */
final class SyncHolidays extends Command
{
    protected $signature = 'admiro:holidays:sync {--year=* : Rok (można powtórzyć), domyślnie bieżący i następny} {--country=* : Kod kraju ISO 3166-1 alpha-2 (można powtórzyć)}';

    protected $description = 'Pobiera święta i dni wolne krajów z Nager.Date';

    public function handle(HolidaySync $sync): int
    {
        $years = array_values(array_filter(array_map(intval(...), $this->strings('year')), static fn(int $year): bool => $year > 1900));
        $years = $years !== [] ? $years : [CarbonImmutable::today()->year, CarbonImmutable::today()->year + 1];
        $codes = array_map(strtoupper(...), $this->strings('country'));
        $query = Country::query();
        $query->where(ColumnNames::IS_ACTIVE, true);

        if ($codes !== []) {
            $query->whereIn('alpha_code_2', $codes);
        }

        $failed = false;

        foreach ($query->get() as $country) {
            foreach ($years as $year) {
                try {
                    $this->info("{$country->alpha_code_2} {$year}: zapisane " . $sync->sync($country, $year));
                } catch (HolidaysUnavailable $exception) {
                    $this->error("{$country->alpha_code_2} {$year}: " . $exception->getMessage());
                    $failed = true;
                }
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function strings(string $option): array
    {
        $values = $this->option($option);

        return array_values(array_filter(is_array($values) ? $values : [], is_string(...)));
    }
}
