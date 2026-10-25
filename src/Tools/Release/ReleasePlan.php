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

namespace Simplysmart\Admiro\Tools\Release;

/**
 * Wynik zaplanowanego wydania: poprzednia i nowa wersja oraz nowa treść CHANGELOG.md.
 */
final readonly class ReleasePlan
{
    public function __construct(
        public SemanticVersion $from,
        public SemanticVersion $to,
        public ReleaseBump $bump,
        public string $changelog,
    ) {}
}
