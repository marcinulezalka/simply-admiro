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

use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Admiro\Permissions\RequiredRole;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\Permissions\OrganizationMember;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\PermissionTables;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Członkowie organizacji: we wszystkich strefach (panel administratora) albo w jednej strefie, także konto
 * po tokenie tylko wtedy, gdy należy do organizacji (inaczej null, czyli 404 w panelu).
 */
final readonly class OrganizationMembers
{
    public function __construct(
        private ZoneRegistry $zones,
        private ZoneRoleNames $roles,
    ) {}

    /**
     * @return list<array{zone: ZoneDefinition, account: ZoneAccount}>
     */
    public function all(Organization $organization): array
    {
        $members = [];

        foreach ($this->zones->all() as $zone) {
            foreach ($this->inZone($organization, $zone, withHidden: true) as $account) {
                $members[] = ['zone' => $zone, 'account' => $account];
            }
        }

        return $members;
    }

    /**
     * Członkowie organizacji w strefie. Konta administratorów — z obowiązkową rolą wsparcia (RequiredRole::Support)
     * w tej organizacji — są ukryte (listy personelu, dzienniki i strony stref); widzi je tylko panel administratorów
     * (all(), $withHidden).
     *
     * @return list<ZoneAccount>
     */
    public function inZone(Organization $organization, ZoneDefinition $zone, bool $withHidden = false): array
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

        $accounts = array_values(array_filter($query->get()->all(), static fn(mixed $account): bool => $account instanceof ZoneAccount));

        return $withHidden ? $accounts : array_values(array_filter(
            $accounts,
            fn(ZoneAccount $account): bool => ! in_array(RequiredRole::Support->value, $this->roles->of($account, $organization->id), true),
        ));
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
