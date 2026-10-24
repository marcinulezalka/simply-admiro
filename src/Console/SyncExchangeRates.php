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

namespace Simplysmart\Mainboard\Console;

use Carbon\CarbonImmutable;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Simplysmart\Mainboard\ExchangeRates\ExchangeRateSync;
use Simplysmart\Mainboard\ExchangeRates\ExchangeRatesUnavailable;

/**
 * Pobiera kursy walut (tabela A NBP) dla aktywnych walut słownika: domyślnie ostatnie
 * `mainboard.exchange_rates.days_back` dni do dziś (uzupełnia braki po świętach i awariach), albo okres --from/--to.
 * Idempotentna; uruchamiana przez scheduler w dni robocze.
 */
final class SyncExchangeRates extends Command
{
    protected $signature = 'mainboard:exchange-rates:sync {--from= : Data początkowa (RRRR-MM-DD)} {--to= : Data końcowa (RRRR-MM-DD), domyślnie dziś}';

    protected $description = 'Pobiera kursy walut z tabeli A NBP';

    public function handle(ExchangeRateSync $sync, Repository $config): int
    {
        $to = $this->date('to') ?? CarbonImmutable::today();
        $from = $this->date('from') ?? $to->subDays($config->integer('mainboard.exchange_rates.days_back'));

        if ($from->gt($to)) {
            $this->error('--from musi być przed --to.');

            return self::INVALID;
        }

        try {
            $this->info("Kursy {$from->toDateString()} – {$to->toDateString()}: zapisane " . $sync->sync($from, $to));
        } catch (ExchangeRatesUnavailable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function date(string $option): ?CarbonImmutable
    {
        $value = $this->option($option);

        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? CarbonImmutable::parse($value) : null;
    }
}
