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

namespace Simplysmart\Mainboard\Database\Seeders;

use Illuminate\Config\Repository;
use Illuminate\Database\Seeder;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;

/**
 * Organizacja operatora aplikacji (stałe id Organization::OPERATOR_ID, nazwa z `app.name`): zakres ról stref
 * bez firm klientów. Seeder referencyjny, idempotentny: przywraca usuniętą, a zmienionej nazwy nie nadpisuje.
 */
final class OperatorOrganizationSeeder extends Seeder
{
    public function __construct(
        private readonly Repository $config,
    ) {}

    public function run(): void
    {
        $organization = Organization::withTrashed()->firstOrNew([ColumnNames::ID => Organization::OPERATOR_ID]);

        if (! $organization->exists) {
            $organization->forceFill([
                ColumnNames::NAME => $this->config->string('app.name'),
                ColumnNames::IS_ACTIVE => true,
                ColumnNames::IS_DEFAULT => true,
            ]);
        }

        $organization->forceFill([ColumnNames::DELETED_AT => null])->save();
    }
}
