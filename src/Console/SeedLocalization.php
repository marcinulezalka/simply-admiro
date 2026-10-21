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
use Simplysmart\Mainboard\Database\Seeders\LocalizationSeeder;

/**
 * Wczytuje słowniki lokalizacji (waluty, języki, kraje, stawki VAT). Seeder referencyjny, idempotentny:
 * wolno go uruchomić ponownie i na produkcji, wybory administratora zostają.
 */
final class SeedLocalization extends Command
{
    protected $signature = 'mainboard:localization:seed';

    protected $description = 'Wczytuje słowniki lokalizacji: waluty, języki, kraje i stawki VAT';

    public function handle(): int
    {
        return $this->call('db:seed', ['--class' => LocalizationSeeder::class, '--force' => true]);
    }
}
