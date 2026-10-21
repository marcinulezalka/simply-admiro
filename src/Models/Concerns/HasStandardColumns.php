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

namespace Simplysmart\Mainboard\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Simplysmart\Mainboard\Database\ColumnNames;

/**
 * Casty kolumn z Columns::base() i Columns::closing(), ich wartości domyślne jako domyślne atrybuty
 * nowego modelu (jak w bazie, więc checksum nowego i odczytanego rekordu jest ta sama) oraz scope active().
 *
 * @method void mergeCasts(array<string, string> $casts) Metoda modelu Eloquent.
 */
trait HasStandardColumns
{
    public function initializeHasStandardColumns(): void
    {
        $this->attributes += [ColumnNames::IS_ACTIVE => false, ColumnNames::SORT_ORDER => 0];
        $this->mergeCasts([
            ColumnNames::TOKEN => 'string',
            ColumnNames::PARENT_ID => 'integer',
            ColumnNames::NAME => 'string',
            ColumnNames::STATUS => 'string',
            ColumnNames::IS_ACTIVE => 'boolean',
            ColumnNames::SORT_ORDER => 'integer',
            ColumnNames::JSON_ATTRIBUTES => 'array',
            ColumnNames::JSON_MIGRATIONS => 'array',
            ColumnNames::CHECKSUM => 'string',
            ColumnNames::JSON_ARCHIVE => 'array',
            ColumnNames::CREATED_AT => 'datetime',
            ColumnNames::UPDATED_AT => 'datetime',
            ColumnNames::DELETED_AT => 'datetime',
        ]);
    }

    /**
     * Rekordy włączone przez administratora; o tym, czy filtrować, decyduje klasa zapytań.
     *
     * @param Builder<static> $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where($query->qualifyColumn(ColumnNames::IS_ACTIVE), true);
    }
}
