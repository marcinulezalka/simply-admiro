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

namespace Simplysmart\Mainboard\Queries\Permissions;

use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Permissions\Role;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Permissions\PermissionTables;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Nazwy ról: globalne role strefy (do formularzy nadawania) i role konta w organizacji.
 */
final readonly class ZoneRoleNames
{
    public function __construct(
        private CurrentOrganization $organization,
    ) {}

    /**
     * @return list<string>
     */
    public function available(ZoneDefinition $zone): array
    {
        $query = Role::query();
        $query->where(PermissionTables::GUARD_NAME, $zone->guard);
        $query->whereNull(PermissionTables::TEAM_FOREIGN_KEY);
        $query->orderBy(ColumnNames::ID);

        return array_values(array_filter($query->pluck(ColumnNames::NAME)->all(), is_string(...)));
    }

    /**
     * @return list<string>
     */
    public function of(ZoneAccount $account, int $organizationId): array
    {
        return $this->organization->within($organizationId, static function () use ($account): array {
            $account->forgetZonePermissions();

            return array_values(array_filter($account->getRoleNames()->all(), is_string(...)));
        });
    }
}
