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
use Simplysmart\Mainboard\Database\Seeders\OperatorOrganizationSeeder;

/**
 * Zakłada organizację operatora aplikacji. Seeder referencyjny, idempotentny: wolno go uruchomić ponownie
 * i na produkcji.
 */
final class SeedOrganizations extends Command
{
    protected $signature = 'mainboard:organizations:seed';

    protected $description = 'Zakłada organizację operatora aplikacji (zakres ról stref bez firm klientów)';

    public function handle(): int
    {
        return $this->call('db:seed', ['--class' => OperatorOrganizationSeeder::class, '--force' => true]);
    }
}
