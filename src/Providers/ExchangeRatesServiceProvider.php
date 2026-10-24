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

namespace Simplysmart\Mainboard\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Mainboard\Console\SyncExchangeRates;
use Simplysmart\Mainboard\Contracts\ExchangeRateProvider;
use Simplysmart\Mainboard\ExchangeRates\NbpTableA;

/**
 * Kursy walut: dostawca kursów (domyślnie tabela A NBP), komenda synchronizacji i jej uruchamianie w dni robocze
 * o godzinie z `mainboard.exchange_rates.schedule_at` (pusta wyłącza harmonogram).
 */
final class ExchangeRatesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bindIf(ExchangeRateProvider::class, NbpTableA::class);
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([SyncExchangeRates::class]);
        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            $at = config()->string('mainboard.exchange_rates.schedule_at');

            if ($at !== '') {
                $schedule->command(SyncExchangeRates::class)->weekdays()->at($at);
            }
        });
    }
}
