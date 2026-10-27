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

namespace Simplysmart\Admiro\Queries\Organizations;

use Illuminate\Database\ConnectionInterface;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Permissions\OrganizationMember;
use Simplysmart\Admiro\Models\Permissions\Role;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\PermissionTables;
use Simplysmart\Admiro\Permissions\RequiredRole;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Przypisania organizacji do stref: strefy, których konta są członkami organizacji, i właściciele
 * (rola `owner` w tej organizacji, konta nieusunięte) — dla wielu organizacji naraz, bez zapytań na organizację.
 */
final readonly class OrganizationAssignments
{
    public function __construct(
        private ZoneRegistry $zones,
        private ConnectionInterface $db,
    ) {}

    /**
     * @param list<int> $organizationIds
     *
     * @return array<int, array{zones: list<ZoneDefinition>, owners: list<array{zone: ZoneDefinition, account: ZoneAccount}>}>
     */
    public function of(array $organizationIds): array
    {
        $zones = [];
        $owners = [];

        if ($organizationIds !== []) {
            foreach ($this->zones->all() as $zone) {
                if (! $zone->requiresLogin()) {
                    continue;
                }

                $type = $this->morphType($zone);

                foreach (array_unique($this->memberOrganizations($type, $organizationIds)) as $organizationId) {
                    $zones[$organizationId][] = $zone;
                }

                foreach ($this->owners($zone, $type, $organizationIds) as [$organizationId, $account]) {
                    $owners[$organizationId][] = ['zone' => $zone, 'account' => $account];
                }
            }
        }

        $assignments = [];

        foreach ($organizationIds as $organizationId) {
            $assignments[$organizationId] = ['zones' => $zones[$organizationId] ?? [], 'owners' => $owners[$organizationId] ?? []];
        }

        return $assignments;
    }

    private function morphType(ZoneDefinition $zone): string
    {
        $model = $zone->accountModel();

        return (new $model())->getMorphClass();
    }

    /**
     * @param list<int> $organizationIds
     *
     * @return list<int>
     */
    private function memberOrganizations(string $type, array $organizationIds): array
    {
        $ids = OrganizationMember::query()
            ->where(PermissionTables::MODEL_TYPE, $type)
            ->whereIn(PermissionTables::TEAM_FOREIGN_KEY, $organizationIds)
            ->orderBy(ColumnNames::ID)
            ->pluck(PermissionTables::TEAM_FOREIGN_KEY)
            ->all();

        return array_values(array_map(intval(...), array_filter($ids, is_numeric(...))));
    }

    /**
     * Właściciele strefy w organizacjach: przypisania roli `owner` guardu strefy i ich nieusunięte konta.
     *
     * @param list<int> $organizationIds
     *
     * @return list<array{int, ZoneAccount}>
     */
    private function owners(ZoneDefinition $zone, string $type, array $organizationIds): array
    {
        $roleIds = Role::query()
            ->where(PermissionTables::GUARD_NAME, $zone->guard)
            ->where(ColumnNames::NAME, RequiredRole::Owner->value)
            ->pluck(ColumnNames::ID)
            ->all();

        if ($roleIds === []) {
            return [];
        }

        $pairs = [];

        foreach ($this->db->table(PermissionTables::MODEL_HAS_ROLES)
            ->whereIn(PermissionTables::ROLE_FOREIGN_KEY, $roleIds)
            ->where(PermissionTables::MODEL_TYPE, $type)
            ->whereIn(PermissionTables::TEAM_FOREIGN_KEY, $organizationIds)
            ->orderBy(ColumnNames::ID)
            ->get([PermissionTables::TEAM_FOREIGN_KEY, PermissionTables::MODEL_ID]) as $row) {
            $organizationId = $row->{PermissionTables::TEAM_FOREIGN_KEY} ?? null;
            $accountId = $row->{PermissionTables::MODEL_ID} ?? null;

            if (is_numeric($organizationId) && is_numeric($accountId)) {
                $pairs[] = [(int) $organizationId, (int) $accountId];
            }
        }

        if ($pairs === []) {
            return [];
        }

        $model = $zone->accountModel();
        $accounts = [];

        $query = $model::query();
        $query->whereIn(ColumnNames::ID, array_column($pairs, 1));

        foreach ($query->get()->all() as $account) {
            $key = $account instanceof ZoneAccount ? $account->getKey() : null;

            if ($account instanceof ZoneAccount && is_numeric($key)) {
                $accounts[(int) $key] = $account;
            }
        }

        $owners = [];

        foreach ($pairs as [$organizationId, $accountId]) {
            if (isset($accounts[$accountId])) {
                $owners[] = [$organizationId, $accounts[$accountId]];
            }
        }

        return $owners;
    }
}
