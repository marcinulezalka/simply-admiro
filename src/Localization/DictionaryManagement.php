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

namespace Simplysmart\Mainboard\Localization;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Enums\TaxRateType;
use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Models\Dictionaries\Currency;
use Simplysmart\Mainboard\Models\Dictionaries\Language;
use Simplysmart\Mainboard\Models\Dictionaries\TaxRate;

/**
 * Zmiany słowników lokalizacji przez administratora: włączanie i wyłączanie krajów, języków i walut (bez wpisów
 * domyślnych i języków interfejsu) oraz stawki VAT: nowa stawka od daty (nowa podstawowa kończy poprzednią
 * podstawową dzień wcześniej) i zakończenie stawki. Zapis przez model: historia i checksum.
 */
final readonly class DictionaryManagement
{
    public function __construct(
        private ConnectionInterface $db,
        private InterfaceLocales $locales,
        private TaxRateLabels $labels,
    ) {}

    /**
     * @throws DictionaryChangeRejected
     */
    public function setActive(Country|Language|Currency $entry, bool $active): void
    {
        if (! $active && $entry->getAttribute(ColumnNames::IS_DEFAULT) === true) {
            throw DictionaryChangeRejected::because('the default entry cannot be disabled.');
        }

        if (! $active && $entry instanceof Language && $entry->is_interface) {
            throw DictionaryChangeRejected::because('an interface language cannot be disabled.');
        }

        $entry->forceFill([ColumnNames::IS_ACTIVE => $active])->save();
        $this->locales->forget();
    }

    /**
     * @throws DictionaryChangeRejected
     */
    public function addTaxRate(Country $country, TaxRateType $type, ?string $rate, CarbonImmutable $validFrom, ?string $note = null): TaxRate
    {
        $rate = $type === TaxRateType::Exempt ? null : $rate;

        if ($type !== TaxRateType::Exempt && ($rate === null || ! is_numeric($rate) || (float) $rate < 0 || (float) $rate > 100)) {
            throw DictionaryChangeRejected::because('a tax rate needs a percentage between 0 and 100.');
        }

        return $this->db->transaction(function () use ($country, $type, $rate, $validFrom, $note): TaxRate {
            if ($type === TaxRateType::Standard) {
                $this->endStandardRatesBefore($country, $validFrom);
            }

            $taxRate = new TaxRate();
            $taxRate->forceFill([
                ...$this->labels->for($rate),
                ColumnNames::IS_ACTIVE => true,
                'country_id' => $country->id,
                'type' => $type,
                'rate' => $rate,
                'valid_from' => $validFrom,
                'note' => $note,
            ])->save();

            return $taxRate;
        });
    }

    /**
     * Ostatni dzień obowiązywania stawki.
     *
     * @throws DictionaryChangeRejected
     */
    public function endTaxRate(TaxRate $rate, CarbonImmutable $validTo): void
    {
        if ($validTo->lt($rate->valid_from)) {
            throw DictionaryChangeRejected::because('a tax rate cannot end before it starts.');
        }

        $rate->forceFill(['valid_to' => $validTo])->save();
    }

    /**
     * Otwarte stawki podstawowe kraju kończą się dzień przed nową; stawka zaczynająca się w tym dniu lub później
     * to konflikt.
     */
    private function endStandardRatesBefore(Country $country, CarbonImmutable $validFrom): void
    {
        $query = TaxRate::query();
        $query->where('country_id', $country->id);
        $query->where('type', TaxRateType::Standard->value);
        $query->whereNull('valid_to');

        foreach ($query->get() as $open) {
            if ($open->valid_from->gte($validFrom)) {
                throw DictionaryChangeRejected::because('a newer standard rate already starts on or after this date.');
            }

            $open->forceFill(['valid_to' => $validFrom->subDay()])->save();
        }
    }
}
