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

namespace Simplysmart\Mainboard\Database\Seeders;

use Illuminate\Config\Repository;
use Simplysmart\Mainboard\Database\IdSlots;
use Simplysmart\Mainboard\Enums\TaxRateType;
use Simplysmart\Mainboard\Localization\DictionaryLocales;
use Simplysmart\Mainboard\Localization\TaxRateLabels;
use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Models\Dictionaries\TaxRate;

/**
 * Stawki VAT państw UE (podstawowa, obniżone, zerowe; Polska w całości z „zw”) — tylko krajom,
 * które istnieją i nie mają jeszcze żadnej stawki, więc zmiany administratora zostają.
 * Stawki się zmieniają: poza Polską mają notę o weryfikacji w bazie TEDB.
 */
final class TaxRatesSeeder extends DictionarySeeder
{
    public function __construct(
        Repository $config,
        IdSlots $idSlots,
        DictionaryLocales $locales,
        private readonly TaxRateLabels $labels,
    ) {
        parent::__construct($config, $idSlots, $locales);
    }

    public function run(): void
    {
        $withRates = TaxRate::withTrashed()->get()->map(static fn(TaxRate $rate): ?int => $rate->country_id)->all();
        $query = Country::query();
        $query->whereIn(Country::codeColumn(), array_keys(LocalizationData::VAT_RATES));
        $countries = $query->get();

        foreach ($countries as $country) {
            if (in_array($country->id, $withRates, true)) {
                continue;
            }

            $rates = LocalizationData::VAT_RATES[$country->alpha_code_2];
            $from = $rates['from'] ?? LocalizationData::VAT_RATES_FROM;

            $this->add($country, TaxRateType::Standard, $rates['standard'], $from);

            foreach ($rates['reduced'] as $rate) {
                $this->add($country, TaxRateType::forReducedPercentage((float) $rate), $rate, $from);
            }

            if ($country->alpha_code_2 === LocalizationData::VAT_VERIFIED_COUNTRY) {
                $this->add($country, TaxRateType::Exempt, null, $from);
            }
        }
    }

    private function add(Country $country, TaxRateType $type, float|int|null $rate, string $from): void
    {
        $taxRate = $this->withSeedId(new TaxRate());
        $taxRate->forceFill([
            ...$this->labels->for($rate),
            'is_active' => true,
            'country_id' => $country->id,
            'type' => $type,
            'rate' => $rate,
            'valid_from' => $from,
            'note' => $country->alpha_code_2 === LocalizationData::VAT_VERIFIED_COUNTRY ? null : LocalizationData::VAT_RATES_NOTE,
        ])->save();
    }
}
