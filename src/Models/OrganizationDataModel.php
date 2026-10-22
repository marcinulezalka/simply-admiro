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

namespace Simplysmart\Mainboard\Models;

use Illuminate\Container\Container;
use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Models\Relations\BelongsToOrganization;
use Simplysmart\Mainboard\Permissions\PermissionTables;

/**
 * Model danych należących do jednej organizacji (kolumna `organization_id` po kolumnach Columns::base()).
 * Wiązanie trasy działa tylko w bieżącej organizacji: rekord innej organizacji albo żądanie bez organizacji
 * daje 404, zanim zadziała uprawnienie czy Policy.
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
        $organization = Container::getInstance()->make(CurrentOrganization::class)->id();
        $query = parent::resolveRouteBindingQuery($query, $value, $field);

        if ($organization === null) {
            $query->whereRaw('1 = 0');
        } else {
            $query->where($this->qualifyColumn(PermissionTables::TEAM_FOREIGN_KEY), $organization);
        }

        return $query;
    }
}
