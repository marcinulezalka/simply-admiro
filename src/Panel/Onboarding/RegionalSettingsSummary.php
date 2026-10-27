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

namespace Simplysmart\Admiro\Panel\Onboarding;

use Carbon\CarbonImmutable;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\TaxRate;
use Simplysmart\Admiro\Queries\Dictionaries\DefaultEntries;
use Simplysmart\Admiro\Queries\Dictionaries\HolidaysIn;
use Simplysmart\Admiro\Queries\Dictionaries\TaxRatesInForce;

/**
 * Ustawienia regionalne przy języku domyślnym w zadaniu „Języki” kreatora onboardingu: kraj i waluta domyślne,
 * podstawowa stawka podatku kraju w mocy dziś i liczba dni wolnych kraju w bieżącym roku.
 */
final readonly class RegionalSettingsSummary
{
    public function __construct(
        private DefaultEntries $defaults,
        private TaxRatesInForce $taxRates,
        private HolidaysIn $holidays,
    ) {}

    /**
     * @return array{country: Country|null, currency: Currency|null, tax: TaxRate|null, holidays: int}
     */
    public function summary(): array
    {
        $country = $this->defaults->country();

        return [
            'country' => $country,
            'currency' => $this->defaults->currency(),
            'tax' => $country === null ? null : $this->taxRates->standard($country),
            'holidays' => $country === null ? 0 : $this->holidays->year($country, CarbonImmutable::now()->year)->count(),
        ];
    }
}
