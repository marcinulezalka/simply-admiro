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
 * Zawartość assetów Core z `public/assets/` do osadzenia inline (logo SVG, style), dopóki paczka nie ma
 * własnego builda serwowanego przez `mainboard:assets` (docs/design-system.md §8).
 */
final class CoreAssets
{
    private const string ROOT = __DIR__ . '/../../public/assets/';

    /**
     * Arkusze Core każdej strony, w kolejności zależności (tokeny → baza → komponenty).
     *
     * @var list<string>
     */
    public const array STYLESHEETS = ['css/tokens.css', 'css/base.css', 'css/app.css'];

    /**
     * Nazwy plików (bez rozszerzenia) w katalogu assetów, posortowane, np. ikony z `img/icons`.
     *
     * @param string $directory Katalog względem `public/assets/`.
     *
     * @return list<string>
     */
    public static function names(string $directory, string $extension): array
    {
        $names = array_map(static fn(string $file): string => basename($file, '.' . $extension), glob(self::ROOT . $directory . '/*.' . $extension) ?: []);
        sort($names);

        return $names;
    }

    /**
     * @param string $path Ścieżka względem `public/assets/`, np. `img/brand/logo.svg`.
     */
    public static function contents(string $path): string
    {
        $contents = @file_get_contents(self::ROOT . $path);

        if ($contents === false) {
            throw new RuntimeException("Core asset [{$path}] does not exist.");
        }

        return $contents;
    }
}
