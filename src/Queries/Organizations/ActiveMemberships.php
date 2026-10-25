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

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\Permissions\OrganizationMember;
use Simplysmart\Mainboard\Permissions\PermissionTables;

/**
 * Aktywne organizacje konta strefy: członkostwo w organizacji aktywnej i nieusuniętej.
 */
final class ActiveMemberships
{
    /**
     * Id organizacji konta, od najstarszego członkostwa.
     *
     * @return list<int>
     */
    public function ids(Model $account): array
    {
        $ids = OrganizationMember::query()
            ->where(PermissionTables::MODEL_TYPE, $account->getMorphClass())
            ->where(PermissionTables::MODEL_ID, $account->getKey())
            ->whereIn(PermissionTables::TEAM_FOREIGN_KEY, Organization::query()->where(ColumnNames::IS_ACTIVE, true)->select(ColumnNames::ID))
            ->orderBy(ColumnNames::ID)
            ->pluck(PermissionTables::TEAM_FOREIGN_KEY)
            ->all();

        return array_values(array_map(intval(...), array_filter($ids, is_numeric(...))));
    }

    /**
     * Aktywne organizacje konta (np. przełącznik organizacji w layoucie strefy) z organizacją nadrzędną
     * (Organization::contextName()), po nazwie.
     *
     * @return Collection<int, Organization>
     */
    public function organizations(Model $account): Collection
    {
        $query = Organization::query();
        $query->with('parent');
        $query->whereIn(ColumnNames::ID, $this->ids($account));
        $query->orderBy(ColumnNames::NAME);

        return $query->get();
    }

    public function includes(Model $account, int $organizationId): bool
    {
        return in_array($organizationId, $this->ids($account), true);
    }
}
