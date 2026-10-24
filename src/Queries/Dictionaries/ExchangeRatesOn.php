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
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Simplysmart\Mainboard\Models\Dictionaries\Currency;
use Simplysmart\Mainboard\Models\Dictionaries\ExchangeRate;

/**
 * Odczyt kursów walut w złotych: kurs z dnia, kurs z ostatniego dnia publikacji przed datą (reguła przeliczeń
 * faktur: ostatni dzień roboczy przed dniem obowiązku podatkowego), przeliczenie kwoty między walutami przez PLN
 * (bcmath, bez błędów zaokrągleń) i lista kursów do panelu (najnowsze najpierw).
 */
final class ExchangeRatesOn
{
    public const string BASE_CURRENCY = 'PLN';

    private const int SCALE = 6;

    public function find(string $token): ?ExchangeRate
    {
        return ExchangeRate::query()->with('currency')->where('token', $token)->first();
    }

    public function on(Currency $currency, CarbonImmutable $date): ?ExchangeRate
    {
        $query = ExchangeRate::query();
        $query->where('currency_id', $currency->id);
        $query->whereDate('rate_date', $date);

        return $query->first();
    }

    public function lastBefore(Currency $currency, CarbonImmutable $date): ?ExchangeRate
    {
        $query = ExchangeRate::query();
        $query->where('currency_id', $currency->id);
        $query->whereDate('rate_date', '<', $date);
        $query->orderByDesc('rate_date');

        return $query->first();
    }

    /**
     * Kwota w walucie `$to` po kursach z ostatniego dnia publikacji przed `$date` (null, gdy brak kursu), zaokrąglona
     * do miejsc po przecinku waluty docelowej.
     *
     * @param numeric-string $amount
     *
     * @return numeric-string|null
     */
    public function convert(string $amount, Currency $from, Currency $to, CarbonImmutable $date): ?string
    {
        $fromRate = $this->plnRate($from, $date);
        $toRate = $this->plnRate($to, $date);

        if ($fromRate === null || $toRate === null) {
            return null;
        }

        $pln = bcmul($amount, $fromRate, self::SCALE);

        return self::round(bcdiv($pln, $toRate, self::SCALE), $to->decimals);
    }

    /**
     * @return LengthAwarePaginator<int, ExchangeRate>
     */
    public function page(?Currency $currency, ?CarbonImmutable $from, ?CarbonImmutable $to, int $perPage): LengthAwarePaginator
    {
        $query = ExchangeRate::query();
        $query->with('currency');

        if ($currency !== null) {
            $query->where('currency_id', $currency->id);
        }

        if ($from !== null) {
            $query->whereDate('rate_date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('rate_date', '<=', $to);
        }

        $query->orderByDesc('rate_date');
        $query->orderBy('currency_id');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return numeric-string|null
     */
    private function plnRate(Currency $currency, CarbonImmutable $date): ?string
    {
        if ($currency->alpha_code_3 === self::BASE_CURRENCY) {
            return '1';
        }

        $rate = $this->lastBefore($currency, $date)?->rate;

        return is_numeric($rate) ? $rate : null;
    }

    /**
     * Zaokrąglenie połówkowe od zera do `$decimals` miejsc.
     *
     * @param numeric-string $value
     *
     * @return numeric-string
     */
    private static function round(string $value, int $decimals): string
    {
        $half = bcdiv('5', bcpow('10', (string) ($decimals + 1)), $decimals + 1);

        return str_starts_with($value, '-') ? bcsub($value, $half, $decimals) : bcadd($value, $half, $decimals);
    }
}
