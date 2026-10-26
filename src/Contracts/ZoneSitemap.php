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

use Simplysmart\Admiro\View\Navigation\NavigationGroup;

/**
 * Opcjonalne rozszerzenie Contracts\ZoneNavigation: mapa strony strefy — wszystkie strony w grupach, także te, których
 * nie ma (jeszcze) w menu bocznym. W layoucie Workspace (Enums\AppLayout) menu boczne pokazuje wtedy tylko
 * ZoneNavigation::groups(), a mapa strony w stopce (i wyszukiwanie stron) — sitemap(); Core dokłada po niej grupę konta.
 */
interface ZoneSitemap
{
    /**
     * Grupy mapy strony z bieżącą pozycją oznaczoną (`current`).
     *
     * @return list<NavigationGroup>
     */
    public function sitemap(): array;
}
