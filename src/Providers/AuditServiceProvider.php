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
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Admiro\Audit\AuditEntries;
use Simplysmart\Admiro\Audit\Listeners\StoreAuditEvent;
use Simplysmart\Admiro\Audit\Listeners\WriteAuditLog;
use Simplysmart\Admiro\Console\PruneAuditEvents;

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
