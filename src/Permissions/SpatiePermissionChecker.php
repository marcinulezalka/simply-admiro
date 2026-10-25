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

namespace Simplysmart\Admiro\Permissions;

use BackedEnum;
use Simplysmart\Admiro\Contracts\HasZoneRoles;
use Simplysmart\Admiro\Contracts\ZonePermissionChecker;
use Simplysmart\Admiro\Zones\ZoneDefinition;
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
