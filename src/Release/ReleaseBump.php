<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/tools.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Tools\Release;

/**
 * Rodzaj podbicia wersji wraz z mapowaniem sekcji Keep a Changelog na to podbicie.
 */
enum ReleaseBump: string
{
    case Patch = 'patch';
    case Minor = 'minor';
    case Major = 'major';

    /**
     * Sekcje Keep a Changelog i podbicie, którego wymaga każdy wpis w danej sekcji.
     *
     * @var array<string, ReleaseBump>
     */
    public const array SECTIONS = [
        'Added' => self::Minor,
        'Changed' => self::Patch,
        'Deprecated' => self::Minor,
        'Removed' => self::Major,
        'Fixed' => self::Patch,
        'Security' => self::Patch,
    ];

    /**
     * Zwraca podbicie wymagane przez sekcję o podanym tytule.
     *
     * @param  string  $title  Tytuł nagłówka „###”, np. „Added”.
     * @return ReleaseBump|null Podbicie albo null, gdy sekcja nie należy do Keep a Changelog.
     */
    public static function forSection(string $title): ?self
    {
        return self::SECTIONS[$title] ?? null;
    }

    /**
     * Zwraca wyższe z dwóch podbić; null traktowane jest jako brak podbicia.
     *
     * @param  ReleaseBump|null  $other  Podbicie do porównania.
     * @return ReleaseBump Wyższe podbicie.
     */
    public function max(?self $other): self
    {
        return $other !== null && $other->rank() > $this->rank() ? $other : $this;
    }

    /**
     * Waga podbicia używana do porównań.
     */
    private function rank(): int
    {
        return match ($this) {
            self::Patch => 1,
            self::Minor => 2,
            self::Major => 3,
        };
    }
}
