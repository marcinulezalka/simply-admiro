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

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Simplysmart\Mainboard\Models\Permissions\PermissionDenial;
use Simplysmart\Mainboard\Permissions\PermissionTables;

/**
 * Odmowy uprawnień konta strefy we wszystkich organizacjach.
 */
trait HasPermissionDenials
{
    /**
     * @return MorphMany<PermissionDenial, $this>
     */
    public function permissionDenials(): MorphMany
    {
        return $this->morphMany(PermissionDenial::class, PermissionTables::MODEL_MORPH);
    }
}
