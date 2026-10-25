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
use Simplysmart\Mainboard\Models\Relations\BelongsToPermission;
use Simplysmart\Mainboard\Models\Relations\BelongsToPermissionHolder;
use Simplysmart\Mainboard\Permissions\PermissionTables;

/**
 * Odmowa uprawnienia kontu strefy w organizacji: wygrywa z uprawnieniem z ról (tabela łącząca
 * `permission_denials`, bez kolumn standardowych, z historią).
 *
 * @property int $id
 * @property int $permission_id
 * @property string $model_type
 * @property int $model_id
 * @property int $organization_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
final class PermissionDenial extends Model
{
    use BelongsToPermission;
    use BelongsToPermissionHolder;

    protected $table = PermissionTables::PERMISSION_DENIALS;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            ColumnNames::ID => 'integer',
            PermissionTables::PERMISSION_FOREIGN_KEY => 'integer',
            PermissionTables::MODEL_TYPE => 'string',
            PermissionTables::MODEL_ID => 'integer',
            PermissionTables::TEAM_FOREIGN_KEY => 'integer',
            ColumnNames::CREATED_AT => 'datetime',
            ColumnNames::UPDATED_AT => 'datetime',
        ];
    }
}
