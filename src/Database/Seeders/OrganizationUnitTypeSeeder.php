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

namespace Simplysmart\Admiro\Database\Seeders;

use Illuminate\Database\Seeder;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Database\IdSlots;
use Simplysmart\Admiro\Models\OrganizationUnitType;

/**
 * Domyślne typy jednostek organizacyjnych w slotach 1–999 (IdSlots::next()): placówka, filia, poradnia, oddział,
 * departament, pracownia, zakład oraz struktura wewnętrzna — dział administracyjny i dział wewnętrzny. Seeder
 * referencyjny, idempotentny po `code`: brakujący typ dodaje, a typu zmienionego w panelu nie nadpisuje.
 */
final class OrganizationUnitTypeSeeder extends Seeder
{
    /**
     * Typy domyślne w kolejności listy: kod => [nazwa, prefiks numeru, ikona, struktura wewnętrzna].
     *
     * @var array<string, array{string, string, string, bool}>
     */
    public const array DEFAULTS = [
        'placowka' => ['Placówka', 'PLA-', 'building', false],
        'filia' => ['Filia', 'FIL-', 'home', false],
        'poradnia' => ['Poradnia', 'POR-', 'heart', false],
        'oddzial' => ['Oddział', 'ODD-', 'activity', false],
        'departament' => ['Departament', 'DEP-', 'grid', false],
        'pracownia' => ['Pracownia', 'PRA-', 'cpu', false],
        'zaklad' => ['Zakład', 'ZAK-', 'clipboard', false],
        'administracja' => ['Dział administracyjny', 'ADM-', 'users', true],
        'dzial' => ['Dział wewnętrzny', 'DZW-', 'settings', true],
    ];

    public function __construct(
        private readonly IdSlots $slots,
    ) {}

    public function run(): void
    {
        $order = 0;

        foreach (self::DEFAULTS as $code => [$name, $prefix, $icon, $internal]) {
            $order += 10;

            if (OrganizationUnitType::withTrashed()->where('code', $code)->exists()) {
                continue;
            }

            (new OrganizationUnitType())->forceFill([
                ColumnNames::ID => $this->slots->next(OrganizationUnitType::TABLE),
                ColumnNames::NAME => $name,
                'code' => $code,
                'number_prefix' => $prefix,
                'icon' => $icon,
                'is_internal' => $internal,
                'is_public' => ! $internal,
                ColumnNames::IS_ACTIVE => true,
                ColumnNames::SORT_ORDER => $order,
            ])->save();
        }
    }
}
