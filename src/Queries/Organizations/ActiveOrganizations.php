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

namespace Simplysmart\Mainboard\Queries\Organizations;

use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;

/**
 * Aktywne organizacje (np. wybór organizacji klienta w formularzach paneli), po nazwie; po tokenie z formularza.
 */
final class ActiveOrganizations
{
    /**
     * @return Collection<int, Organization>
     */
    public function all(): Collection
    {
        $query = Organization::query();
        $query->where(ColumnNames::IS_ACTIVE, true);
        $query->orderBy(ColumnNames::NAME);

        return $query->get();
    }

    public function find(string $token): ?Organization
    {
        $query = Organization::query();
        $query->where(ColumnNames::IS_ACTIVE, true);
        $query->where(ColumnNames::TOKEN, $token);

        return $query->first();
    }
}
