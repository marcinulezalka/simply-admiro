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

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\LoginChallengeRepository;
use Simplysmart\Mainboard\Contracts\TrustedDeviceRepository;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Zones\ZoneDefinition;
use Simplysmart\Mainboard\Zones\ZoneTable;

/**
 * Usuwa stare kody logowania oraz odwołane i wygasłe zaufane urządzenia każdej strefy z logowaniem
 * (planowane co godzinę przez Core). Strefa bez tabeli urządzeń (migracje strefy nie zostały uruchomione)
 * dostaje ostrzeżenie zamiast przerwania czyszczenia pozostałych stref.
 */
final class PruneSignInChallenges extends Command
{
    protected $signature = 'mainboard:auth:prune';

    protected $description = 'Usuwa wygasłe kody logowania i nieaktywne zaufane urządzenia stref';

    public function handle(ZoneRegistry $zones, CurrentZone $current, LoginChallengeRepository $challenges, TrustedDeviceRepository $devices, DatabaseManager $db): int
    {
        $schema = $db->connection()->getSchemaBuilder();

        foreach ($zones->all() as $zone) {
            if (! $zone->requiresLogin()) {
                continue;
            }

            if (! $schema->hasTable(ZoneTable::TrustedDevices->for($zone))) {
                $this->warn("{$zone->name}: brak tabeli zaufanych urządzeń (uruchom migracje strefy).");
            }

            [$codes, $removedDevices] = $current->within($zone, static fn(ZoneDefinition $zone): array => [
                $challenges->pruneCreatedBefore(Carbon::now()->subHours($zone->auth()->challengePruneHours)),
                $schema->hasTable(ZoneTable::TrustedDevices->for($zone)) ? $devices->pruneInactiveBefore(Carbon::now()) : 0,
            ]);

            $this->info("{$zone->name}: {$codes} / {$removedDevices}");
        }

        return self::SUCCESS;
    }
}
