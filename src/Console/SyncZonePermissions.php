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
use Simplysmart\Admiro\Permissions\InvalidPermissionFile;
use Simplysmart\Admiro\Permissions\PermissionFileParser;
use Simplysmart\Admiro\Permissions\PermissionFiles;
use Simplysmart\Admiro\Permissions\ZoneRoleSet;
use Simplysmart\Admiro\Permissions\ZoneRoleSynchronizer;

/**
 * Zapisuje role i uprawnienia stref z plików JSON (PermissionFiles). Najpierw waliduje wszystkie pliki, więc
 * błędny plik nie zapisuje niczego. Idempotentna; uprawnienia i role spoza plików tylko raportuje, a usuwa
 * je `--prune` (z potwierdzeniem, na produkcji dodatkowo `--force`).
 */
final class SyncZonePermissions extends Command
{
    protected $signature = 'admiro:permissions:sync
        {--zone= : Tylko ta strefa}
        {--prune : Usuń uprawnienia i role strefy, których nie ma w pliku}
        {--force : Pozwól na --prune na produkcji bez pytania}';

    protected $description = 'Zapisuje role i uprawnienia stref z plików JSON';

    public function handle(PermissionFiles $files, PermissionFileParser $parser, ZoneRoleSynchronizer $synchronizer): int
    {
        try {
            $sets = array_map($parser->parse(...), $files->all());
        } catch (InvalidPermissionFile $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $zone = $this->option('zone');
        $sets = array_filter($sets, static fn(ZoneRoleSet $set): bool => ! is_string($zone) || $set->zone === $zone);

        if ($sets === []) {
            $this->error('Brak plików ról dla wybranej strefy.');

            return self::FAILURE;
        }

        $prune = $this->option('prune') === true;

        if ($prune && ! $this->confirmPrune()) {
            return self::FAILURE;
        }

        foreach ($sets as $set) {
            $report = $synchronizer->sync($set, $prune);
            $this->info("{$report->zone}: nowe " . count($report->created) . ', przywrócone ' . count($report->restored));

            foreach ($report->stale as $stale) {
                $prune ? $this->warn("  usunięte: {$stale}") : $this->warn("  spoza pliku: {$stale} (usuń przez --prune)");
            }
        }

        return self::SUCCESS;
    }

    private function confirmPrune(): bool
    {
        if ($this->option('force') === true) {
            return true;
        }

        if ($this->laravel->environment('production') === true) {
            $this->error('Na produkcji --prune wymaga --force.');

            return false;
        }

        return $this->confirm('Usunąć uprawnienia i role, których nie ma w plikach?');
    }
}
