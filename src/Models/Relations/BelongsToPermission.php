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

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Simplysmart\Mainboard\Models\Permissions\Permission;
use Simplysmart\Mainboard\Permissions\PermissionTables;

/**
 * Uprawnienie rekordu (kolumna `permission_id`).
 */
trait BelongsToPermission
{
    /**
     * @return BelongsTo<Permission, $this>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, PermissionTables::PERMISSION_FOREIGN_KEY);
    }
}
