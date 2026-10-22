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

namespace Simplysmart\Admiro\Enums;

/**
 * Uprawnienia strefy administratorów; przypisania do ról są w pliku ról Core
 * (`database/seeders/permissions/admiro.json` w simplysmart/mainboard), etykiety w `permissions.admiro.*`.
 */
enum Permission: string
{
    case ZonesView = 'zones.view';
    case ZonesConfigure = 'zones.configure';
    case OwnersView = 'owners.view';
    case OwnersAssign = 'owners.assign';
    case SettingsView = 'settings.view';
    case SettingsManage = 'settings.manage';
    case UsersManage = 'users.manage';
    case AuditView = 'audit.view';
    case HeroesManage = 'heroes.manage';
    case SystemMaintenance = 'system.maintenance';
}
