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

namespace Simplysmart\Admiro\Contracts;

use Simplysmart\Admiro\View\Navigation\NavigationItem;

/**
 * Opcjonalne rozszerzenie Contracts\ZoneNavigation: skróty strefy w stopce layoutu Workspace (Enums\AppLayout) —
 * ikony-odnośniki między kontem a wyszukiwarką (np. dashboard, ulubione, pulpit roboczy). Bez niego stopka ich nie ma.
 */
interface ZoneShortcuts
{
    /**
     * Skróty z bieżącą stroną oznaczoną (`current`); etykieta jest podpowiedzią i tekstem dla czytników ekranu.
     *
     * @return list<NavigationItem>
     */
    public function shortcuts(): array;
}
