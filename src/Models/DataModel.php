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

namespace Simplysmart\Mainboard\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Simplysmart\Mainboard\Models\Concerns\HasChecksum;
use Simplysmart\Mainboard\Models\Concerns\HasStandardColumns;

/**
 * Baza modeli tabel z kolumnami standardowymi (Database\Columns): token jako klucz trasy, soft delete,
 * casty kolumn standardowych, checksum rekordu i scope active() (docs/baza-danych.md §5).
 *
 * Model jest czysty: tabela, typy i relacje (traity z Models\Relations); zapis w Actions, odczyt w Queries.
 *
 * @property int $id
 * @property string $token
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $status
 * @property bool $is_active
 * @property int $sort_order
 * @property array<array-key, mixed>|null $json_attributes
 * @property array<array-key, mixed>|null $json_migrations
 * @property string|null $checksum
 * @property array<array-key, mixed>|null $json_archive
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
abstract class DataModel extends MainModel
{
    use HasChecksum;
    use HasStandardColumns;
    use SoftDeletes;
}
