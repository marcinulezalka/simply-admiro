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
use Simplysmart\Admiro\Console\SyncHolidays;
use Simplysmart\Admiro\Contracts\HolidayProvider;
use Simplysmart\Admiro\Holidays\NagerDate;

/**
 * Święta i dni wolne krajów: dostawca świąt (domyślnie Nager.Date), komenda synchronizacji i jej uruchamianie
 * w pierwszym dniu miesiąca (gdy `admiro.holidays.schedule` włączone).
 */
final class HolidaysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bindIf(HolidayProvider::class, NagerDate::class);
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([SyncHolidays::class]);
        $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
            if (config()->boolean('admiro.holidays.schedule')) {
                $schedule->command(SyncHolidays::class)->monthly();
            }
        });
    }
}
