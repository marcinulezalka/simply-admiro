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

use Simplysmart\Admiro\Models\Organization;

/**
 * Opcjonalne rozszerzenie Contracts\ZoneNavigation: dokąd prowadzi jednostka konta z listy „Twoje jednostki” w menu
 * konta layoutu stref (Zones\ZoneContexts::units()) — np. dane jednostki w ustawieniach strefy; bez rozszerzenia
 * (albo przy null) pulpit jednostki w jej kontekście.
 */
interface ZoneUnitLinks
{
    /**
     * Adres danych jednostki dla konta pracującego w niej w roli $role (pulpit tej jednostki: $homeUrl) albo null.
     */
    public function unitUrl(Organization $organization, ?string $role, string $homeUrl): ?string;
}
