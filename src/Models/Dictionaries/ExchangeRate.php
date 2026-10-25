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

namespace Simplysmart\Mainboard\Models\Dictionaries;

use Carbon\CarbonImmutable;
use Simplysmart\Mainboard\Enums\ExchangeRateSource;
use Simplysmart\Mainboard\Models\DataModel;
use Simplysmart\Mainboard\Models\Relations\BelongsToCurrency;

/**
 * Kurs waluty w złotych z dnia `rate_date` (PLN za 1 jednostkę waluty, 6 miejsc po przecinku): z tabeli NBP
 * (`table_number`, np. `204/A/NBP/2026`) albo ręczny. `name` to etykieta („EUR 2026-10-23”).
 *
 * @property int $currency_id
 * @property CarbonImmutable $rate_date
 * @property string $rate
 * @property ExchangeRateSource $source
 * @property string|null $table_number
 * @property-read Currency $currency
 */
final class ExchangeRate extends DataModel
{
    use BelongsToCurrency;

    public const string TABLE = 'locale_exchange_rates';

    protected $table = self::TABLE;

    /**
     * Etykieta kursu w kolumnie `name` („EUR 2026-10-23”).
     */
    public static function nameFor(Currency $currency, CarbonImmutable $date): string
    {
        return $currency->alpha_code_3 . ' ' . $date->toDateString();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'currency_id' => 'integer',
            'rate_date' => 'immutable_date',
            'rate' => 'decimal:6',
            'source' => ExchangeRateSource::class,
            'table_number' => 'string',
        ];
    }
}
