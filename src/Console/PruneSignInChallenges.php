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
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\LoginChallengeRepository;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;

/**
 * Usuwa stare kody logowania każdej strefy z logowaniem (planowane co godzinę przez Core).
 */
final class PruneSignInChallenges extends Command
{
    protected $signature = 'mainboard:auth:prune';

    protected $description = 'Usuwa wygasłe kody logowania stref';

    public function handle(ZoneRegistry $zones, CurrentZone $current, LoginChallengeRepository $challenges): int
    {
        foreach ($zones->all() as $zone) {
            if (! $zone->requiresLogin()) {
                continue;
            }

            $current->set($zone);
            $deleted = $challenges->pruneCreatedBefore(Carbon::now()->subHours($zone->auth()->challengePruneHours));
            $current->forget();

            $this->info("{$zone->name}: {$deleted}");
        }

        return self::SUCCESS;
    }
}
