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

namespace Simplysmart\Mainboard\ExchangeRates;

use Carbon\CarbonImmutable;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Enums\ExchangeRateSource;
use Simplysmart\Mainboard\Models\Dictionaries\Currency;
use Simplysmart\Mainboard\Models\Dictionaries\ExchangeRate;

/**
 * Kursy ręczne administratora (np. przy awarii NBP): dodanie kursu waluty z dnia, którego jeszcze nie ma,
 * i usunięcie kursu ręcznego (kurs z NBP usuwa się tylko z bazą).
 */
final readonly class ManualExchangeRates
{
    private const int RATE_SCALE = 6;

    /**
     * @throws ExchangeRatesRejected Gdy kurs nie jest dodatni albo waluta ma już kurs z tego dnia.
     */
    public function addManual(Currency $currency, CarbonImmutable $date, string $rate): ExchangeRate
    {
        if (! is_numeric($rate) || (float) $rate <= 0) {
            throw ExchangeRatesRejected::because('an exchange rate must be a positive number.');
        }

        if (ExchangeRate::query()->where('currency_id', $currency->id)->whereDate('rate_date', $date)->exists()) {
            throw ExchangeRatesRejected::because('the currency already has a rate on this day.');
        }

        $entry = new ExchangeRate();
        $entry->forceFill([
            ColumnNames::NAME => ExchangeRate::nameFor($currency, $date),
            ColumnNames::IS_ACTIVE => true,
            'currency_id' => $currency->id,
            'rate_date' => $date,
            'rate' => number_format((float) $rate, self::RATE_SCALE, '.', ''),
            'source' => ExchangeRateSource::Manual,
        ])->save();

        return $entry;
    }

    /**
     * @throws ExchangeRatesRejected Gdy kurs pochodzi z NBP.
     */
    public function removeManual(ExchangeRate $rate): void
    {
        if ($rate->source !== ExchangeRateSource::Manual) {
            throw ExchangeRatesRejected::because('only manual rates can be removed.');
        }

        $rate->delete();
    }
}
