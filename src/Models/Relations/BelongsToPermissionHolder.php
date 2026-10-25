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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Simplysmart\Admiro\Permissions\PermissionTables;

/**
 * Konto strefy, którego dotyczy rekord uprawnień (kolumny `model_type`, `model_id`).
 */
trait BelongsToPermissionHolder
{
    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo(PermissionTables::MODEL_MORPH);
    }
}
