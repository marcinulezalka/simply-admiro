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

namespace Simplysmart\Mainboard\Holidays;

use Carbon\CarbonImmutable;
use Illuminate\Config\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Simplysmart\Mainboard\Contracts\HolidayProvider;

/**
 * Święta z publicznego API Nager.Date (`/api/v3/PublicHolidays/{rok}/{kraj}`, bez klucza; wysyłany jest tylko kod
 * kraju i rok): tylko święta ogólnokrajowe (`global`) typu „Public”, czyli dni wolne od pracy. 404 to kraj
 * nieobsługiwany. Adres i limit czasu z configu.
 */
final readonly class NagerDate implements HolidayProvider
{
    private const int NOT_FOUND = 404;

    private const string PUBLIC_TYPE = 'Public';

    public function __construct(
        private HttpFactory $http,
        private Repository $config,
    ) {}

    public function forYear(string $countryCode, int $year): array
    {
        $url = rtrim($this->config->string('mainboard.holidays.nager_url'), '/') . '/PublicHolidays/' . $year . '/' . strtoupper($countryCode);

        try {
            $response = $this->http->acceptJson()->timeout($this->config->integer('mainboard.holidays.timeout_seconds'))->get($url);
        } catch (ConnectionException $exception) {
            throw HolidaysUnavailable::because($exception->getMessage());
        }

        if ($response->status() === self::NOT_FOUND || $response->status() === 204) {
            return [];
        }

        $entries = $response->successful() ? $response->json() : null;

        if (! is_array($entries)) {
            throw HolidaysUnavailable::because('Nager.Date responded with status ' . $response->status() . '.');
        }

        $quotes = [];

        foreach ($entries as $entry) {
            if (! is_array($entry) || ! is_string($entry['date'] ?? null) || ($entry['global'] ?? false) !== true) {
                continue;
            }

            $types = is_array($entry['types'] ?? null) ? $entry['types'] : [];

            if (! in_array(self::PUBLIC_TYPE, $types, true)) {
                continue;
            }

            $english = is_string($entry['name'] ?? null) ? $entry['name'] : $entry['date'];
            $quotes[] = new HolidayQuote(CarbonImmutable::parse($entry['date']), is_string($entry['localName'] ?? null) ? $entry['localName'] : $english, $english);
        }

        return $quotes;
    }
}
