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

namespace Simplysmart\Admiro\Database;

/**
 * Wzorzec wyszukiwania `LIKE` „zawiera” dla porównań bez rozróżniania wielkości liter (`LOWER(kolumna) LIKE ?`): tekst
 * małymi literami, znaki `%` i `_` (symbole LIKE) zamienione na spacje, otoczony `%`; pusty tekst daje null.
 */
final class LikePattern
{
    public static function contains(string $term): ?string
    {
        $term = mb_strtolower(trim(str_replace(['%', '_'], ' ', $term)));

        return $term === '' ? null : '%' . $term . '%';
    }
}
