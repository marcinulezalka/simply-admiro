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
use Illuminate\Config\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Simplysmart\Admiro\Contracts\ExchangeRateProvider;

/**
 * Kursy średnie z tabeli A NBP (Web API `api.nbp.pl`, JSON): okres dzielony na zapytania po najwyżej 93 dni (limit
 * API), odpowiedź 404 to okres bez publikacji (weekend, święto), nie błąd. Adres i limit czasu z configu.
 */
final readonly class NbpTableA implements ExchangeRateProvider
{
    /**
     * Najdłuższy okres jednego zapytania w API NBP (dni).
     */
    private const int MAX_DAYS = 93;

    private const int NOT_FOUND = 404;

    public function __construct(
        private HttpFactory $http,
        private Repository $config,
    ) {}

    public function between(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $quotes = [];

        for ($start = $from->startOfDay(); $start->lte($to); $start = $start->addDays(self::MAX_DAYS)) {
            $end = $start->addDays(self::MAX_DAYS - 1)->min($to->startOfDay());
            array_push($quotes, ...$this->fetch($start, $end));
        }

        return $quotes;
    }

    /**
     * @return list<ExchangeRateQuote>
     *
     * @throws ExchangeRatesUnavailable
     */
    private function fetch(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $url = rtrim($this->config->string('admiro.exchange_rates.nbp_url'), '/') . '/exchangerates/tables/A/' . $from->toDateString() . '/' . $to->toDateString() . '/';

        try {
            $response = $this->http->acceptJson()->timeout($this->config->integer('admiro.exchange_rates.timeout_seconds'))->get($url, ['format' => 'json']);
        } catch (ConnectionException $exception) {
            throw ExchangeRatesUnavailable::because($exception->getMessage());
        }

        if ($response->status() === self::NOT_FOUND) {
            return [];
        }

        $tables = $response->successful() ? $response->json() : null;

        if (! is_array($tables)) {
            throw ExchangeRatesUnavailable::because('NBP responded with status ' . $response->status() . '.');
        }

        $quotes = [];

        foreach ($tables as $table) {
            if (! is_array($table) || ! is_string($table['effectiveDate'] ?? null) || ! is_array($table['rates'] ?? null)) {
                throw ExchangeRatesUnavailable::because('unexpected NBP table format.');
            }

            $date = CarbonImmutable::parse($table['effectiveDate']);
            $number = is_string($table['no'] ?? null) ? $table['no'] : null;

            foreach ($table['rates'] as $rate) {
                if (is_array($rate) && is_string($rate['code'] ?? null) && is_numeric($rate['mid'] ?? null)) {
                    $quotes[] = new ExchangeRateQuote(strtoupper($rate['code']), $date, number_format((float) $rate['mid'], 6, '.', ''), $number);
                }
            }
        }

        return $quotes;
    }
}
