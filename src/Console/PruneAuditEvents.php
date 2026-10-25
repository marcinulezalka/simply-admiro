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

namespace Simplysmart\Admiro\Console;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Models\AuditEvent;

/**
 * Usuwa wpisy dziennika zdarzeń starsze niż `admiro.audit.retention_days` (planowane codziennie przez Core).
 */
final class PruneAuditEvents extends Command
{
    protected $signature = 'admiro:audit:prune';

    protected $description = 'Usuwa stare wpisy dziennika zdarzeń';

    public function handle(Repository $config): int
    {
        $before = Carbon::now()->subDays($config->integer('admiro.audit.retention_days'));
        $deleted = AuditEvent::query()->toBase()->where('occurred_at', '<', $before)->delete();

        $this->info("audit_events: {$deleted}");

        return self::SUCCESS;
    }
}
