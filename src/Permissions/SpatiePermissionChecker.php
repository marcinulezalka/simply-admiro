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

namespace Simplysmart\Mainboard\Permissions;

use BackedEnum;
use Simplysmart\Mainboard\Contracts\HasZoneRoles;
use Simplysmart\Mainboard\Contracts\ZonePermissionChecker;
use Simplysmart\Mainboard\Zones\ZoneDefinition;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

/**
 * Adapter spatie/laravel-permission: uprawnienie sprawdzane w guardzie strefy.
 */
final readonly class SpatiePermissionChecker implements ZonePermissionChecker
{
    public function allows(ZoneDefinition $zone, HasZoneRoles $account, BackedEnum|string $permission): bool
    {
        try {
            return $account->hasPermissionTo($permission, $zone->guard);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }
}
