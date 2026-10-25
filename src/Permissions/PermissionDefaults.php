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

namespace Simplysmart\Mainboard\Permissions;

use Illuminate\Contracts\Config\Repository;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\Permissions\Permission;
use Simplysmart\Mainboard\Models\Permissions\Role;
use Simplysmart\Mainboard\Zones\ZoneCacheKey;

/**
 * Konfiguracja spatie/laravel-permission ustawiana przez Core (bez publikowania config/permission.php):
 * modele Core, zespoły = organizacje, nazwy tabel i klucz cache wspólny dla stref.
 *
 * Bieżącej organizacji nie ma domyślnie (fail closed): bez niej przypisanie roli jest odrzucane przez bazę,
 * a sprawdzenie uprawnienia daje false. Ustawia ją strefa dla żądania (organizacja operatora w strefach bez firm).
 *
 * Wywoływana po register() wszystkich providerów (config spatie jest już scalony), pojedynczymi kluczami.
 */
final readonly class PermissionDefaults
{
    public const string CACHE_SCOPE = 'shared';

    private const string CACHE_SEGMENT = 'spatie.permission';

    public function __construct(
        private Repository $config,
    ) {}

    public function apply(): void
    {
        $this->config->set([
            'permission.models.permission' => Permission::class,
            'permission.models.role' => Role::class,
            'permission.models.team' => Organization::class,
            'permission.teams' => true,
            'permission.column_names.team_foreign_key' => PermissionTables::TEAM_FOREIGN_KEY,
            'permission.column_names.model_morph_key' => PermissionTables::MODEL_ID,
            'permission.table_names.permissions' => PermissionTables::PERMISSIONS,
            'permission.table_names.roles' => PermissionTables::ROLES,
            'permission.table_names.model_has_permissions' => PermissionTables::MODEL_HAS_PERMISSIONS,
            'permission.table_names.model_has_roles' => PermissionTables::MODEL_HAS_ROLES,
            'permission.table_names.role_has_permissions' => PermissionTables::ROLE_HAS_PERMISSIONS,
            'permission.cache.key' => ZoneCacheKey::make(self::CACHE_SCOPE, self::CACHE_SEGMENT),
        ]);
    }
}
