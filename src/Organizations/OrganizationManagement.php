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

namespace Simplysmart\Mainboard\Organizations;

use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;

/**
 * Zakładanie i zmiany organizacji (OrganizationData: nazwa, nazwa skrócona, dane firmy; firma → oddział przez
 * `parent_id`): drzewo bez cykli, organizacja nadrzędna musi być aktywna, a organizacji operatora nie wolno wyłączyć
 * ani przenieść pod inną. Nowa organizacja bez e-maila do rozliczeń dostaje e-mail organizacji. Wyłączona organizacja
 * znika z wyboru kont (ActiveMemberships), więc jej członkowie tracą w niej uprawnienia.
 *
 * Departament (createDepartment(), Organization::IS_DEPARTMENT) leży zawsze bezpośrednio pod jednostką — organizacją
 * bez flagi — i nie ma pod sobą żadnej organizacji; flagi nie zmienia się po założeniu.
 */
final class OrganizationManagement
{
    /**
     * @throws OrganizationChangeRejected
     */
    public function create(OrganizationData $data, ?Organization $parent = null): Organization
    {
        return $this->store($data, $parent, false);
    }

    /**
     * Departament jednostki: osobny kontekst członków i ról, dane w jednostce (OrganizationUnits).
     *
     * @throws OrganizationChangeRejected
     */
    public function createDepartment(OrganizationData $data, Organization $unit): Organization
    {
        return $this->store($data, $unit, true);
    }

    /**
     * @throws OrganizationChangeRejected
     */
    public function update(Organization $organization, OrganizationData $data, ?Organization $parent): void
    {
        if ($parent !== null && $organization->id === Organization::OPERATOR_ID) {
            throw OrganizationChangeRejected::because('the operator organization cannot have a parent.');
        }

        if ($parent === null && $organization->isDepartment()) {
            throw OrganizationChangeRejected::because('a department must stay under its unit.');
        }

        $this->ensureActiveParent($parent);
        $this->ensureUnitParent($parent);
        $this->ensureNoCycle($organization, $parent);

        $organization->forceFill([
            ColumnNames::NAME => $data->name,
            Organization::SHORT_NAME => $data->shortName,
            ...$data->columns(),
            ColumnNames::PARENT_ID => $parent?->id,
        ])->save();
    }

    /**
     * @throws OrganizationChangeRejected
     */
    private function store(OrganizationData $data, ?Organization $parent, bool $department): Organization
    {
        $this->ensureActiveParent($parent);
        $this->ensureUnitParent($parent);

        $columns = $data->columns();
        $columns[Organization::BILLING_EMAIL] ??= $columns[Organization::EMAIL];

        $organization = new Organization();
        $organization->forceFill([
            ColumnNames::NAME => $data->name,
            Organization::SHORT_NAME => $data->shortName,
            ...$columns,
            ColumnNames::PARENT_ID => $parent?->id,
            Organization::IS_DEPARTMENT => $department,
            ColumnNames::IS_ACTIVE => true,
        ])->save();

        return $organization;
    }

    /**
     * @throws OrganizationChangeRejected
     */
    public function setActive(Organization $organization, bool $active): void
    {
        if (! $active && $organization->id === Organization::OPERATOR_ID) {
            throw OrganizationChangeRejected::because('the operator organization cannot be disabled.');
        }

        $organization->forceFill([ColumnNames::IS_ACTIVE => $active])->save();
    }

    private function ensureActiveParent(?Organization $parent): void
    {
        if ($parent !== null && $parent->getAttribute(ColumnNames::IS_ACTIVE) !== true) {
            throw OrganizationChangeRejected::because('the parent organization is disabled.');
        }
    }

    private function ensureUnitParent(?Organization $parent): void
    {
        if ($parent?->isDepartment() === true) {
            throw OrganizationChangeRejected::because('a department cannot have organizations under it.');
        }
    }

    private function ensureNoCycle(Organization $organization, ?Organization $parent): void
    {
        $visited = [];

        for ($current = $parent; $current !== null; $current = $this->parentOf($current)) {
            if ($current->id === $organization->id || in_array($current->id, $visited, true)) {
                throw OrganizationChangeRejected::because('the organization cannot be placed under itself or its branch.');
            }

            $visited[] = $current->id;
        }
    }

    private function parentOf(Organization $organization): ?Organization
    {
        $parentId = $organization->getAttribute(ColumnNames::PARENT_ID);

        return is_int($parentId) ? Organization::query()->find($parentId) : null;
    }
}
