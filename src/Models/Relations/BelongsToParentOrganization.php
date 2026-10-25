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

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;

/**
 * Organizacja nadrzędna (kolumna `parent_id`).
 */
trait BelongsToParentOrganization
{
    /**
     * @return BelongsTo<Organization, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Organization::class, ColumnNames::PARENT_ID);
    }
}
