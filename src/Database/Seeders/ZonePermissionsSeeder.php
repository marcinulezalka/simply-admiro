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

namespace Simplysmart\Admiro\Database\Seeders;

use Illuminate\Database\Seeder;
use Simplysmart\Admiro\Permissions\PermissionFileParser;
use Simplysmart\Admiro\Permissions\PermissionFiles;
use Simplysmart\Admiro\Permissions\ZoneRoleSynchronizer;

/**
 * Seeder referencyjny ról i uprawnień wszystkich stref z plików JSON (bez usuwania rekordów spoza plików);
 * na wdrożeniu używa się komendy `admiro:permissions:sync`.
 */
final class ZonePermissionsSeeder extends Seeder
{
    public function __construct(
        private readonly PermissionFiles $files,
        private readonly PermissionFileParser $parser,
        private readonly ZoneRoleSynchronizer $synchronizer,
    ) {}

    public function run(): void
    {
        foreach (array_map($this->parser->parse(...), $this->files->all()) as $set) {
            $this->synchronizer->sync($set);
        }
    }
}
