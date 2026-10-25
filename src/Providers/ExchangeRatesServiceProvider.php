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

namespace Simplysmart\Admiro\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Admiro\Console\SyncExchangeRates;
use Simplysmart\Admiro\Contracts\ExchangeRateProvider;
use Simplysmart\Admiro\ExchangeRates\NbpTableA;

/**
 * Kursy walut: dostawca kursów (domyślnie tabela A NBP), komenda synchronizacji i jej uruchamianie w dni robocze
 * o godzinie z `admiro.exchange_rates.schedule_at` (pusta wyłącza harmonogram).
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
            $at = config()->string('admiro.exchange_rates.schedule_at');

            if ($at !== '') {
                $schedule->command(SyncExchangeRates::class)->weekdays()->at($at);
            }
        });
    }
}
