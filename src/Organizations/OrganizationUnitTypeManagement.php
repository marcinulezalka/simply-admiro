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

namespace Simplysmart\Admiro\Organizations;

use Illuminate\Support\Str;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\OrganizationUnitType;

/**
 * Jedyna droga zmian słownika typów jednostek (strefa administratorów) i typu jednostki (strefy): dodanie typu
 * (kod z nazwy, unikalny), zmiana nazwy, prefiksu, ikony i znaczników, włączenie/wyłączenie (wyłączony typ znika
 * z wyboru, a jednostki go zachowują) oraz ustawienie typu jednostki przez model, więc historia ma jeden wpis.
 */
final class OrganizationUnitTypeManagement
{
    /**
     * @param array{name: string, number_prefix: string, icon: string, is_internal: bool, is_public: bool} $data
     */
    public function create(array $data): OrganizationUnitType
    {
        $type = new OrganizationUnitType();
        $type->forceFill([
            ...$this->attributes($data),
            'code' => $this->uniqueCode($data['name']),
            ColumnNames::IS_ACTIVE => true,
            ColumnNames::SORT_ORDER => $this->lastSortOrder() + 10,
        ])->save();

        return $type;
    }

    /**
     * @param array{name: string, number_prefix: string, icon: string, is_internal: bool, is_public: bool} $data
     */
    public function update(OrganizationUnitType $type, array $data): void
    {
        $type->forceFill($this->attributes($data))->save();
    }

    public function setActive(OrganizationUnitType $type, bool $active): void
    {
        $type->forceFill([ColumnNames::IS_ACTIVE => $active])->save();
    }

    /**
     * Typ jednostki (null — bez typu).
     */
    public function assign(Organization $unit, ?OrganizationUnitType $type): void
    {
        $unit->forceFill([Organization::UNIT_TYPE_ID => $type?->id])->save();
        $unit->setRelation('unitType', $type);
    }

    /**
     * @param array{name: string, number_prefix: string, icon: string, is_internal: bool, is_public: bool} $data
     *
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            ColumnNames::NAME => $data['name'],
            'number_prefix' => $data['number_prefix'],
            'icon' => in_array($data['icon'], Organization::ICONS, true) ? $data['icon'] : Organization::ICONS[0],
            'is_internal' => $data['is_internal'],
            'is_public' => $data['is_public'],
        ];
    }

    private function lastSortOrder(): int
    {
        $last = OrganizationUnitType::query()->max(ColumnNames::SORT_ORDER);

        return is_numeric($last) ? (int) $last : 0;
    }

    private function uniqueCode(string $name): string
    {
        $base = Str::limit(Str::slug($name, '_'), 24, '') ?: 'typ';
        $code = $base;

        for ($suffix = 2; OrganizationUnitType::withTrashed()->where('code', $code)->exists(); $suffix++) {
            $code = $base . '_' . $suffix;
        }

        return $code;
    }
}
