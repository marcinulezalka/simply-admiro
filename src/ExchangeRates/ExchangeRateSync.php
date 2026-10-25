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

namespace Simplysmart\Admiro\ExchangeRates;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Simplysmart\Admiro\Contracts\ExchangeRateProvider;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\ExchangeRateSource;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\ExchangeRate;

/**
 * Zapisuje kursy dostawcy (ExchangeRateProvider) z okresu dla aktywnych walut słownika: idempotentnie (kurs waluty
 * z dnia aktualizowany), bez nadpisywania kursów ręcznych i bez waluty bazowej PLN.
 */
final readonly class ExchangeRateSync
{
    private const string BASE_CURRENCY = 'PLN';

    public function __construct(
        private ExchangeRateProvider $provider,
        private ConnectionInterface $db,
    ) {}

    /**
     * @return int Liczba zapisanych (nowych albo zmienionych) kursów.
     *
     * @throws ExchangeRatesUnavailable
     */
    public function sync(CarbonImmutable $from, CarbonImmutable $to): int
    {
        $currencies = Currency::query()->where(ColumnNames::IS_ACTIVE, true)->where('alpha_code_3', '!=', self::BASE_CURRENCY)->get()->keyBy('alpha_code_3');
        $quotes = array_filter($this->provider->between($from, $to), static fn(ExchangeRateQuote $quote): bool => $currencies->has($quote->currencyCode));

        return $this->db->transaction(static function () use ($quotes, $currencies): int {
            $saved = 0;

            foreach ($quotes as $quote) {
                $currency = $currencies->get($quote->currencyCode);

                if (! $currency instanceof Currency) {
                    continue;
                }

                $query = ExchangeRate::query();
                $query->where('currency_id', $currency->id);
                $query->whereDate('rate_date', $quote->date);
                $rate = $query->first() ?? new ExchangeRate();

                if ($rate->exists && $rate->source === ExchangeRateSource::Manual) {
                    continue;
                }

                $rate->forceFill([
                    ColumnNames::NAME => ExchangeRate::nameFor($currency, $quote->date),
                    ColumnNames::IS_ACTIVE => true,
                    'currency_id' => $currency->id,
                    'rate_date' => $quote->date,
                    'rate' => $quote->rate,
                    'source' => ExchangeRateSource::Nbp,
                    'table_number' => $quote->tableNumber,
                ]);

                if ($rate->isDirty()) {
                    $rate->save();
                    $saved++;
                }
            }

            return $saved;
        });
    }
}
