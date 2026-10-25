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

use Illuminate\Console\Command;
use Simplysmart\Admiro\Database\Seeders\LegalDocumentsSeeder;

/**
 * Publikuje pierwsze wersje polityk (cookies, prywatności, RODO) z treści Core. Seeder referencyjny, idempotentny:
 * wolno go uruchomić ponownie i na produkcji, dokumenty z wersją albo szkicem zostają.
 */
final class SeedLegalDocuments extends Command
{
    protected $signature = 'admiro:legal:seed';

    protected $description = 'Publikuje pierwsze wersje polityki cookies, polityki prywatności i klauzuli RODO';

    public function handle(): int
    {
        return $this->call('db:seed', ['--class' => LegalDocumentsSeeder::class, '--force' => true]);
    }
}
