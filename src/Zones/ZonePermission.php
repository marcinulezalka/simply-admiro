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

namespace Simplysmart\Admiro\Zones;

use BackedEnum;
use Simplysmart\Admiro\Http\Middleware\RequireZonePermission;

/**
 * Middleware trasy wymagającej uprawnienia w strefie, np. w `zone.php`:
 * `Route::get('settings', ...)->middleware(ZonePermission::middleware(Permission::SettingsView))`.
 */
final class ZonePermission
{
    public static function middleware(BackedEnum|string $permission): string
    {
        return RequireZonePermission::ALIAS . ':' . ($permission instanceof BackedEnum ? (string) $permission->value : $permission);
    }
}
