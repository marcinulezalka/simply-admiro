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

namespace Simplysmart\Admiro\Queries\Permissions;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Permissions\Role;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\PermissionTables;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Nazwy ról: globalne role strefy (do formularzy nadawania), liczba uprawnień każdej z nich i role konta w organizacji.
 */
final readonly class ZoneRoleNames
{
    public function __construct(
        private CurrentOrganization $organization,
        private ConnectionInterface $db,
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
     * Liczba uprawnień każdej globalnej roli strefy (rola → liczba), np. do kafelków profili.
     *
     * @return array<string, int>
     */
    public function permissionCounts(ZoneDefinition $zone): array
    {
        $counts = [];

        foreach ($this->available($zone) as $role) {
            $query = $this->db->table(PermissionTables::ROLE_HAS_PERMISSIONS);
            $query->whereIn(PermissionTables::ROLE_FOREIGN_KEY, static function (Builder $roles) use ($zone, $role): void {
                $roles->select(ColumnNames::ID)->from(PermissionTables::ROLES)
                    ->where(PermissionTables::GUARD_NAME, $zone->guard)
                    ->where(ColumnNames::NAME, $role)
                    ->whereNull(PermissionTables::TEAM_FOREIGN_KEY)
                    ->whereNull(ColumnNames::DELETED_AT);
            });
            $counts[$role] = $query->count();
        }

        return $counts;
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
