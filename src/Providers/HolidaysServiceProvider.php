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
use Simplysmart\Mainboard\Console\SyncHolidays;
use Simplysmart\Mainboard\Contracts\HolidayProvider;
use Simplysmart\Mainboard\Holidays\NagerDate;

/**
 * Święta i dni wolne krajów: dostawca świąt (domyślnie Nager.Date), komenda synchronizacji i jej uruchamianie
 * w pierwszym dniu miesiąca (gdy `mainboard.holidays.schedule` włączone).
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
            if (config()->boolean('mainboard.holidays.schedule')) {
                $schedule->command(SyncHolidays::class)->monthly();
            }
        });
    }
}
