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

namespace Simplysmart\Admiro\Permissions;

/**
 * Rejestr plików ról i uprawnień stref (JSON, format w docs/baza-danych.md §15). Core rejestruje swój katalog
 * `database/seeders/permissions/`, a paczka strefy z własnymi danymi (np. prywatna) dodaje plik lub katalog
 * w swoim providerze, bez zmian w Core.
 */
final class PermissionFiles
{
    private const string EXTENSION = '.json';

    /**
     * @var list<string>
     */
    private array $paths = [];

    /**
     * Plik `*.json` albo katalog takich plików.
     */
    public function add(string $path): void
    {
        $this->paths[] = rtrim($path, '/');
    }

    /**
     * Wszystkie zarejestrowane pliki, katalogi rozwinięte i posortowane.
     *
     * @return list<string>
     */
    public function all(): array
    {
        $files = [];

        foreach ($this->paths as $path) {
            $found = is_dir($path) ? glob($path . '/*' . self::EXTENSION) : [$path];
            $files = [...$files, ...($found === false ? [] : $found)];
        }

        sort($files);

        return array_values(array_unique($files));
    }
}
