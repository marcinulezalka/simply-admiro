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

namespace Simplysmart\Admiro\Models\Relations;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Simplysmart\Admiro\Models\Permissions\PermissionDenial;
use Simplysmart\Admiro\Permissions\PermissionTables;

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
