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

namespace Simplysmart\Mainboard\Queries\Organizations;

use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\Permissions\OrganizationMember;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Permissions\PermissionTables;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Członkowie organizacji: we wszystkich strefach (panel administratora) albo w jednej strefie, także konto
 * po tokenie tylko wtedy, gdy należy do organizacji (inaczej null, czyli 404 w panelu).
 */
final readonly class OrganizationMembers
{
    public function __construct(
        private ZoneRegistry $zones,
    ) {}

    /**
     * @return list<array{zone: ZoneDefinition, account: ZoneAccount}>
     */
    public function all(Organization $organization): array
    {
        $members = [];

        foreach ($this->zones->all() as $zone) {
            foreach ($this->inZone($organization, $zone) as $account) {
                $members[] = ['zone' => $zone, 'account' => $account];
            }
        }

        return $members;
    }

    /**
     * @return list<ZoneAccount>
     */
    public function inZone(Organization $organization, ZoneDefinition $zone): array
    {
        if (! $zone->requiresLogin()) {
            return [];
        }

        $model = $zone->accountModel();
        $ids = OrganizationMember::query()
            ->where(PermissionTables::TEAM_FOREIGN_KEY, $organization->id)
            ->where(PermissionTables::MODEL_TYPE, (new $model())->getMorphClass())
            ->pluck(PermissionTables::MODEL_ID)
            ->all();

        $query = $model::query();
        $query->whereIn(ColumnNames::ID, $ids);
        $query->orderBy(ColumnNames::NAME);

        return array_values(array_filter($query->get()->all(), static fn(mixed $account): bool => $account instanceof ZoneAccount));
    }

    public function find(Organization $organization, ZoneDefinition $zone, string $token): ?ZoneAccount
    {
        foreach ($this->inZone($organization, $zone) as $account) {
            if ($account->getRouteKey() === $token) {
                return $account;
            }
        }

        return null;
    }
}
