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

use DateTimeImmutable;

/**
 * Planuje wydanie na podstawie changelogu i bieżącej wersji — bez żadnych operacji wejścia/wyjścia.
 */
final readonly class ReleasePlanner
{
    /**
     * @param  Changelog  $changelog  Bieżąca treść CHANGELOG.md.
     * @param  SemanticVersion  $current  Bieżąca wersja z pliku VERSION.
     * @param  DateTimeImmutable  $releasedAt  Moment wydania.
     * @param  bool  $majorConfirmed  Czy użytkownik potwierdził podbicie MAJOR.
     * @return ReleasePlan|null Plan wydania albo null, gdy w „[Unreleased]” nie ma żadnych wpisów.
     *
     * @throws ReleaseException Gdy changelog jest niepoprawny albo podbicie MAJOR nie zostało potwierdzone.
     */
    public function plan(Changelog $changelog, SemanticVersion $current, DateTimeImmutable $releasedAt, bool $majorConfirmed): ?ReleasePlan
    {
        $bump = $changelog->requiredBump();

        if ($bump === null) {
            return null;
        }

        if ($bump === ReleaseBump::Major && ! $majorConfirmed) {
            throw new ReleaseException('Wykryto breaking change (bump MAJOR). Uruchom z --major-confirm, aby zastosować.');
        }

        $to = $current->bump($bump);

        return new ReleasePlan($current, $to, $bump, $changelog->promote($to, $releasedAt));
    }
}
