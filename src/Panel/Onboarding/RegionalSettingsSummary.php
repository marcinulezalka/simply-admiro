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

use Illuminate\Database\Query\JoinClause;
use Simplysmart\Admiro\Models\Dictionaries\CountryLanguage;
use Simplysmart\Admiro\Models\Dictionaries\Language;
use Carbon\CarbonImmutable;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\TaxRate;
use Simplysmart\Admiro\Queries\Dictionaries\DefaultEntries;
use Simplysmart\Admiro\Queries\Dictionaries\HolidaysIn;
use Simplysmart\Admiro\Queries\Dictionaries\TaxRatesInForce;

/**
 * Ustawienia regionalne przy językach w zadaniu „Języki” kreatora onboardingu: zapisane (kraj i waluta domyślne,
 * summary()) albo proponowane dla języka (forLanguage(): najludniejszy kraj, w którym język jest urzędowy — najpierw
 * jako główny — i jego waluta); do tego podstawowa stawka podatku kraju w mocy dziś i liczba dni wolnych kraju
 * w bieżącym roku.
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
        return $this->of($this->defaults->country(), $this->defaults->currency());
    }

    /**
     * Propozycja ustawień regionalnych dla języka (kraj bez języka urzędowego — same puste pola).
     *
     * @return array{country: Country|null, currency: Currency|null, tax: TaxRate|null, holidays: int}
     */
    public function forLanguage(Language $language): array
    {
        $countries = (new Country())->getTable();
        $query = Country::query();
        $query->with('currency');
        $query->join(CountryLanguage::TABLE, static function (JoinClause $join) use ($countries, $language): void {
            $join->on(CountryLanguage::TABLE . '.country_id', '=', $countries . '.id')->where(CountryLanguage::TABLE . '.language_id', '=', $language->getKey());
        });
        $query->orderByDesc(CountryLanguage::TABLE . '.' . CountryLanguage::IS_PRIMARY);
        $query->orderByDesc($countries . '.population');
        $query->select($countries . '.*');
        $country = $query->first();

        return $this->of($country, $country?->currency);
    }

    /**
     * @return array{country: Country|null, currency: Currency|null, tax: TaxRate|null, holidays: int}
     */
    private function of(?Country $country, ?Currency $currency): array
    {
        return [
            'country' => $country,
            'currency' => $currency,
            'tax' => $country === null ? null : $this->taxRates->standard($country),
            'holidays' => $country === null ? 0 : $this->holidays->year($country, CarbonImmutable::now()->year)->count(),
        ];
    }
}
