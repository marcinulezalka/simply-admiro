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

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Models\AuditEvent;

/**
 * Usuwa wpisy dziennika zdarzeń starsze niż `mainboard.audit.retention_days` (planowane codziennie przez Core).
 */
final class PruneAuditEvents extends Command
{
    protected $signature = 'mainboard:audit:prune';

    protected $description = 'Usuwa stare wpisy dziennika zdarzeń';

    public function handle(Repository $config): int
    {
        $before = Carbon::now()->subDays($config->integer('mainboard.audit.retention_days'));
        $deleted = AuditEvent::query()->toBase()->where('occurred_at', '<', $before)->delete();

        $this->info("audit_events: {$deleted}");

        return self::SUCCESS;
    }
}
