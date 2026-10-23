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
 * Zakładanie i zmiany organizacji (firma → oddział przez `parent_id`): drzewo bez cykli, organizacja nadrzędna
 * musi być aktywna, a organizacji operatora nie wolno wyłączyć ani przenieść pod inną. Wyłączona organizacja
 * znika z wyboru kont (ActiveMemberships), więc jej członkowie tracą w niej uprawnienia.
 */
final class OrganizationManagement
{
    /**
     * @throws OrganizationChangeRejected
     */
    public function create(string $name, ?Organization $parent = null): Organization
    {
        $this->ensureActiveParent($parent);

        $organization = new Organization();
        $organization->forceFill([
            ColumnNames::NAME => $name,
            ColumnNames::PARENT_ID => $parent?->id,
            ColumnNames::IS_ACTIVE => true,
        ])->save();

        return $organization;
    }

    /**
     * @throws OrganizationChangeRejected
     */
    public function update(Organization $organization, string $name, ?Organization $parent): void
    {
        if ($parent !== null && $organization->id === Organization::OPERATOR_ID) {
            throw OrganizationChangeRejected::because('the operator organization cannot have a parent.');
        }

        $this->ensureActiveParent($parent);
        $this->ensureNoCycle($organization, $parent);

        $organization->forceFill([ColumnNames::NAME => $name, ColumnNames::PARENT_ID => $parent?->id])->save();
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
