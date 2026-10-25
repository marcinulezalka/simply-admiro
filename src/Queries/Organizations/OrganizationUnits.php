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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;

/**
 * Jednostki i ich departamenty (Organization::IS_DEPARTMENT). Jednostka to organizacja bez flagi departamentu; dane
 * departamentu (OrganizationDataModel) należą do jego jednostki, więc kontekst departamentu czyta i zapisuje dane
 * jednostki, a uprawnienia sprawdza w departamencie.
 */
final readonly class OrganizationUnits
{
    public function __construct(
        private CurrentOrganization $organization,
    ) {}

    /**
     * Id jednostki organizacji: dla departamentu jego organizacja nadrzędna, dla jednostki ona sama, dla braku
     * albo nieznanej organizacji null.
     */
    public function unitIdOf(?int $organizationId): ?int
    {
        if ($organizationId === null) {
            return null;
        }

        $row = Organization::query()->whereKey($organizationId)->first([ColumnNames::ID, ColumnNames::PARENT_ID, Organization::IS_DEPARTMENT]);

        if ($row === null) {
            return null;
        }

        $parentId = $row->getAttribute(ColumnNames::PARENT_ID);

        return $row->isDepartment() ? (is_int($parentId) ? $parentId : null) : $row->id;
    }

    /**
     * Jednostka bieżącej organizacji żądania (CurrentOrganization).
     */
    public function currentUnitId(): ?int
    {
        return $this->unitIdOf($this->organization->id());
    }

    /**
     * Aktywne departamenty jednostki, po nazwie.
     *
     * @return Collection<int, Organization>
     */
    public function departments(Organization $unit): Collection
    {
        $query = $this->departmentQuery($unit);
        $query->where(ColumnNames::IS_ACTIVE, true);
        $query->orderBy(ColumnNames::NAME);

        return $query->get();
    }

    /**
     * Departament jednostki po tokenie (także wyłączony) albo null.
     */
    public function department(Organization $unit, string $token): ?Organization
    {
        $query = $this->departmentQuery($unit);
        $query->where(ColumnNames::TOKEN, $token);

        return $query->first();
    }

    /**
     * @return Builder<Organization>
     */
    private function departmentQuery(Organization $unit): Builder
    {
        $query = Organization::query();
        $query->where(ColumnNames::PARENT_ID, $unit->id);
        $query->where(Organization::IS_DEPARTMENT, true);

        return $query;
    }
}
