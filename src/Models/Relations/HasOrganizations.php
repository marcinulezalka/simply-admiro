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

namespace Simplysmart\Mainboard\Models\Relations;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Permissions\PermissionTables;

/**
 * Organizacje, których członkiem jest konto strefy (tabela `organization_members`).
 */
trait HasOrganizations
{
    /**
     * @return MorphToMany<Organization, $this>
     */
    public function organizations(): MorphToMany
    {
        return $this->morphToMany(
            Organization::class,
            PermissionTables::MODEL_MORPH,
            PermissionTables::ORGANIZATION_MEMBERS,
            PermissionTables::MODEL_ID,
            PermissionTables::TEAM_FOREIGN_KEY,
        )->withTimestamps();
    }
}
