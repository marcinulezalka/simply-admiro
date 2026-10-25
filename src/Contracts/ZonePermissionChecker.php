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

namespace Simplysmart\Mainboard\Contracts;

use BackedEnum;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Sprawdzenie uprawnienia konta w strefie, zawsze z jawnie nazwanym guardem strefy. Strefy zależą od tego
 * kontraktu, nie od spatie.
 */
interface ZonePermissionChecker
{
    /**
     * Czy konto ma uprawnienie w strefie w bieżącej organizacji; nieznane uprawnienie to brak dostępu.
     */
    public function allows(ZoneDefinition $zone, HasZoneRoles $account, BackedEnum|string $permission): bool;
}
