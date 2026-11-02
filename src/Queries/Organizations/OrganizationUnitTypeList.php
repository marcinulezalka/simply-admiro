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

namespace Simplysmart\Admiro\Queries\Organizations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\OrganizationUnitType;

/**
 * Typy jednostek organizacyjnych w kolejności listy (`sort_order`, potem nazwa): wszystkie (panel administratorów)
 * albo aktywne (wybór typu jednostki w strefach); typ po tokenie albo kodzie.
 */
final class OrganizationUnitTypeList
{
    /**
     * @return Collection<int, OrganizationUnitType>
     */
    public function all(): Collection
    {
        return $this->ordered()->get();
    }

    /**
     * @return Collection<int, OrganizationUnitType>
     */
    public function active(): Collection
    {
        $query = $this->ordered();
        $query->where(ColumnNames::IS_ACTIVE, true);

        return $query->get();
    }

    /**
     * Aktywny typ po tokenie (np. z formularza strefy); nieznany albo wyłączony — null.
     */
    public function findActive(string $token): ?OrganizationUnitType
    {
        $query = OrganizationUnitType::query();
        $query->where(ColumnNames::IS_ACTIVE, true);
        $query->where(ColumnNames::TOKEN, $token);

        return $query->first();
    }

    public function byCode(string $code): ?OrganizationUnitType
    {
        return OrganizationUnitType::query()->where('code', $code)->first();
    }

    /**
     * @return Builder<OrganizationUnitType>
     */
    private function ordered(): Builder
    {
        $query = OrganizationUnitType::query();
        $query->orderBy(ColumnNames::SORT_ORDER);
        $query->orderBy(ColumnNames::NAME);

        return $query;
    }
}
