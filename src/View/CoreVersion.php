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

namespace Simplysmart\Mainboard\View;

use RuntimeException;

/**
 * Wersja Core z pliku `VERSION` paczki (zapisywanego przez `version-release` z simplysmart/admiro), np. do
 * plakietki na stronie startowej. Composer przy instalacji z repozytorium ścieżkowego zna tylko `dev-main`.
 */
final class CoreVersion
{
    private const string FILE = __DIR__ . '/../../VERSION';

    /**
     * @return string Wersja w formacie MAJOR.MINOR.PATCH, np. `0.1.0`.
     *
     * @throws RuntimeException Gdy pliku VERSION nie ma albo jest pusty.
     */
    public static function current(): string
    {
        $version = trim((string) @file_get_contents(self::FILE));

        if ($version === '') {
            throw new RuntimeException('Core VERSION file is missing or empty.');
        }

        return $version;
    }
}
