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
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\OrganizationUnitType;

/**
 * Typ jednostki organizacyjnej (kolumna Organization::UNIT_TYPE_ID, słownik OrganizationUnitType).
 */
trait BelongsToUnitType
{
    /**
     * @return BelongsTo<OrganizationUnitType, $this>
     */
    public function unitType(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnitType::class, Organization::UNIT_TYPE_ID);
    }
}
