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
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Mainboard\Audit\AuditEntries;
use Simplysmart\Mainboard\Audit\Listeners\StoreAuditEvent;
use Simplysmart\Mainboard\Audit\Listeners\WriteAuditLog;
use Simplysmart\Mainboard\Console\PruneAuditEvents;

/**
 * Dziennik zdarzeń: każde zdarzenie z AuditEntries::EVENTS do pliku i tabeli `audit_events`, czyszczenie codziennie.
 */
final class AuditServiceProvider extends ServiceProvider
{
    public function boot(Dispatcher $events): void
    {
        $events->listen(AuditEntries::EVENTS, WriteAuditLog::class);
        $events->listen(AuditEntries::EVENTS, StoreAuditEvent::class);

        if ($this->app->runningInConsole()) {
            $this->commands([PruneAuditEvents::class]);
            $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
                $schedule->command(PruneAuditEvents::class)->daily();
            });
        }
    }
}
