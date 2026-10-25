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

namespace Simplysmart\Admiro\Models\Permissions;

use Illuminate\Database\Eloquent\SoftDeletes;
use Simplysmart\Admiro\Models\Concerns\HasChecksum;
use Simplysmart\Admiro\Models\Concerns\HasRouteToken;
use Simplysmart\Admiro\Models\Concerns\HasStandardColumns;
use Simplysmart\Admiro\Permissions\PermissionTables;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Uprawnienie strefy (`zasób.akcja`, guard strefy w `guard_name`).
 *
 * Spatie wymaga dziedziczenia po swoim modelu, więc standard DataModel (token, casty, checksum, soft delete)
 * dają te same traity Core. Usunięte uprawnienie dalej zajmuje swoją nazwę w guardzie (unikalny indeks).
 *
 * @property int $id
 * @property string $token
 * @property int|null $parent_id
 * @property string $name
 * @property string|null $status
 * @property bool $is_active
 * @property bool $is_default
 * @property int $sort_order
 * @property string $guard_name
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
final class Permission extends SpatiePermission
{
    use HasChecksum;
    use HasRouteToken;
    use HasStandardColumns;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            PermissionTables::GUARD_NAME => 'string',
        ];
    }
}
