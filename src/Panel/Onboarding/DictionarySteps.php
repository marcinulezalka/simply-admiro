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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\App;
use IntlTimeZone;
use Locale;
use Simplysmart\Admiro\Queries\Dictionaries\DefaultEntries;
use Simplysmart\Admiro\Localization\Dictionary;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\ExchangeRate;
use Simplysmart\Admiro\Models\Dictionaries\Holiday;
use Simplysmart\Admiro\Models\Dictionaries\TaxRate;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Queries\Dictionaries\DictionaryList;
use Simplysmart\Admiro\Queries\Dictionaries\DictionarySearch;
use Simplysmart\Admiro\Queries\Dictionaries\ExchangeRatesOn;
use Simplysmart\Admiro\Queries\Dictionaries\HolidaysIn;
use Simplysmart\Admiro\Queries\Dictionaries\TaxRatesInForce;

/**
 * Dane zadań słownikowych kreatora onboardingu — „Kraje”, „Waluty”, „Kalendarz i święta”, „Stawki podatkowe” — na wzór
 * zadania „Języki”: wszystkie i włączone wpisy (domyślny pierwszy), wyniki wyszukiwania (`q`) i fakty wiersza: kraj —
 * kontynent, region UN M49 (nazwa z intl), ludność, UE i Schengen, czas lokalny (strefy z ICU), dni wolne w bieżącym
 * roku, podstawowa stawka VAT;
 * waluta — ostatni kurs NBP względem waluty domyślnej; brak kraju lub waluty domyślnej (`defaultMissing`); kalendarz — dni wolne roku
 * i najbliższy; podatki — stawki w mocy dziś.
 */
final readonly class DictionarySteps
{
    private const int SEARCH_LIMIT = 30;

    public function __construct(
        private DictionaryList $dictionaries,
        private DictionarySearch $search,
        private TaxRatesInForce $taxRates,
        private HolidaysIn $holidays,
        private ExchangeRatesOn $rates,
        private DefaultEntries $defaults,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function data(OnboardingStep $step, string $term): array
    {
        $kind = $step === OnboardingStep::Currencies ? Dictionary::Currencies : Dictionary::Countries;
        $all = $this->dictionaries->entries($kind);
        $defaultFirst = static fn(Country|Currency $entry): array => [$entry->is_default === true ? 0 : 1, $entry->translatedName()];
        $enabled = $all->filter(static fn(Country|Currency $entry): bool => $entry->is_active === true)->sortBy($defaultFirst)->values();
        $found = $term === ''
            ? new Collection()
            : ($kind === Dictionary::Currencies ? $this->search->currencies($term, self::SEARCH_LIMIT) : $this->search->countries($term, self::SEARCH_LIMIT));
        $year = CarbonImmutable::now()->year;
        $facts = [];

        foreach ([...$enabled->all(), ...$found->all()] as $entry) {
            if ($entry instanceof Currency) {
                $facts[$entry->alpha_code_3] ??= $this->currencyFacts($entry);
            } elseif ($entry instanceof Country) {
                $facts[$entry->alpha_code_2] ??= $this->countryFacts($entry, $year);
            }
        }

        return [
            'term' => $term,
            'dictionary' => $kind,
            'all' => $all,
            'enabled' => $enabled,
            'found' => $found->sortBy($defaultFirst)->values(),
            'facts' => $facts,
            'year' => $year,
            'baseCurrency' => $this->defaults->currency(),
            'defaultMissing' => ! $all->contains(static fn(Country|Currency $entry): bool => $entry->is_default === true),
        ];
    }

    /**
     * @return array{rate: ExchangeRate|null}
     */
    private function currencyFacts(Currency $currency): array
    {
        return ['rate' => $this->rates->lastBefore($currency, CarbonImmutable::tomorrow())];
    }

    /**
     * @return array{currency: Currency|null, region: string|null, time: array{zones: int, earliest: CarbonImmutable, latest: CarbonImmutable}|null, holidays: int, next: Holiday|null, tax: TaxRate|null, taxes: SupportCollection<int, TaxRate>}
     */
    private function countryFacts(Country $country, int $year): array
    {
        $holidays = $this->holidays->year($country, $year);
        $today = CarbonImmutable::today();
        $currency = $country->currency;

        $region = $country->region_code !== null && $country->region_code !== '' ? (string) Locale::getDisplayRegion('und-' . $country->region_code, App::getLocale()) : '';

        return [
            'currency' => $currency instanceof Currency ? $currency : null,
            'region' => $region !== '' && $region !== $country->region_code ? $region : null,
            'time' => $this->localTime($country),
            'holidays' => $holidays->count(),
            'next' => $holidays->first(static fn(Holiday $holiday): bool => $holiday->holiday_date->greaterThanOrEqualTo($today)),
            'tax' => $this->taxRates->standard($country),
            'taxes' => $this->taxRates->forCountry($country),
        ];
    }

    /**
     * Czas lokalny kraju teraz: strefy czasowe kraju z ICU (IntlTimeZone), najwcześniejszy i najpóźniejszy czas
     * spośród nich (jedna strefa — oba równe) i liczba różnych przesunięć; kraj bez stref w ICU — null.
     *
     * @return array{zones: int, earliest: CarbonImmutable, latest: CarbonImmutable}|null
     */
    private function localTime(Country $country): ?array
    {
        $now = CarbonImmutable::now();
        $times = [];

        foreach (IntlTimeZone::createTimeZoneIDEnumeration(IntlTimeZone::TYPE_CANONICAL_LOCATION, $country->alpha_code_2) as $zone) {
            if (is_string($zone)) {
                $time = $now->setTimezone($zone);
                $times[$time->getOffset()] = $time;
            }
        }

        if ($times === []) {
            return null;
        }

        ksort($times);

        return ['zones' => count($times), 'earliest' => reset($times), 'latest' => end($times)];
    }
}
