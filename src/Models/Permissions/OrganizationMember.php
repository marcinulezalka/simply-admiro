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

namespace Simplysmart\Mainboard\Models\Permissions;

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Relations\BelongsToOrganization;
use Simplysmart\Mainboard\Models\Relations\BelongsToPermissionHolder;
use Simplysmart\Mainboard\Permissions\PermissionTables;

/**
 * Członkostwo konta strefy w organizacji (tabela łącząca `organization_members`, bez kolumn standardowych,
 * z historią). Niezależne od ról: członek bez ról nie ma żadnych uprawnień.
 *
 * @property int $id
 * @property int $organization_id
 * @property string $model_type
 * @property int $model_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class OrganizationMember extends Model
{
    use BelongsToOrganization;
    use BelongsToPermissionHolder;

    protected $table = PermissionTables::ORGANIZATION_MEMBERS;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            ColumnNames::ID => 'integer',
            PermissionTables::TEAM_FOREIGN_KEY => 'integer',
            PermissionTables::MODEL_TYPE => 'string',
            PermissionTables::MODEL_ID => 'integer',
            ColumnNames::CREATED_AT => 'datetime',
            ColumnNames::UPDATED_AT => 'datetime',
        ];
    }
}
