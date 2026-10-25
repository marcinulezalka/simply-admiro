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

namespace Simplysmart\Admiro\Models;

use Illuminate\Container\Container;
use Simplysmart\Admiro\Models\Relations\BelongsToOrganization;
use Simplysmart\Admiro\Permissions\PermissionTables;
use Simplysmart\Admiro\Queries\Organizations\OrganizationUnits;

/**
 * Model danych należących do jednej organizacji (kolumna `organization_id` po kolumnach Columns::base()).
 * Wiązanie trasy działa tylko w jednostce bieżącej organizacji (w departamencie — w jego jednostce,
 * Queries\Organizations\OrganizationUnits): rekord innej jednostki albo żądanie bez organizacji daje 404, zanim
 * zadziała uprawnienie czy Policy.
 *
 * @property int $organization_id
 */
abstract class OrganizationDataModel extends DataModel
{
    use BelongsToOrganization;

    /**
     * @param mixed $value
     * @param string|null $field
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        $organization = Container::getInstance()->make(OrganizationUnits::class)->currentUnitId();
        $query = parent::resolveRouteBindingQuery($query, $value, $field);

        if ($organization === null) {
            $query->whereRaw('1 = 0');
        } else {
            $query->where($this->qualifyColumn(PermissionTables::TEAM_FOREIGN_KEY), $organization);
        }

        return $query;
    }
}
