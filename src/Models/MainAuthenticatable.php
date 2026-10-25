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

namespace Simplysmart\Admiro\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Simplysmart\Admiro\Models\Concerns\HasAvatarColor;
use Simplysmart\Admiro\Models\Concerns\HasChecksum;
use Simplysmart\Admiro\Models\Concerns\HasStandardColumns;

/**
 * Baza modeli kont stref (każda strefa ma własną tabelę kont i własny model). Tabela kont ma pełny standard
 * tabel danych (Database\ZoneTables), więc model dostaje to samo co DataModel: casty kolumn standardowych,
 * checksum, soft delete, scope'y active() i default() oraz defaultSetting().
 *
 * @property int $id
 * @property string $token
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $status
 * @property bool $is_active
 * @property bool $is_default
 * @property int $sort_order
 * @property array<array-key, mixed>|null $json_attributes
 * @property array<array-key, mixed>|null $json_default_settings
 * @property list<string>|null $json_tags
 * @property array<array-key, mixed>|null $json_migrations
 * @property string|null $checksum
 * @property array<array-key, mixed>|null $json_archive
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
abstract class MainAuthenticatable extends MainModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable;
    use Authorizable;
    use HasAvatarColor;
    use HasChecksum;
    use HasStandardColumns;
    use SoftDeletes;
}
