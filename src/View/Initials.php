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

/**
 * Inicjały do awatara z imienia i nazwiska albo nazwy: pierwsza litera pierwszego i ostatniego słowa zaczynającego
 * się wielką literą (tytuły pisane małą literą, np. „dr”, są pomijane), np. „dr Anna Wiśniewska” → „AW”.
 */
final class Initials
{
    public static function of(string $name): string
    {
        $words = array_values(array_filter(
            preg_split('/[\s\-]+/u', trim($name)) ?: [],
            static fn(string $word): bool => preg_match('/^\p{Lu}/u', $word) === 1,
        ));

        if ($words === []) {
            return mb_strtoupper(mb_substr(trim($name), 0, 2));
        }

        $first = mb_substr($words[0], 0, 1);

        return mb_strtoupper(count($words) > 1 ? $first . mb_substr($words[count($words) - 1], 0, 1) : $first);
    }
}
