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

/**
 * Nazwy tabel i kolumn uprawnień (spatie/laravel-permission z zespołami = organizacjami); czytają je
 * migracja Core, konfiguracja spatie (PermissionDefaults) i modele.
 */
final class PermissionTables
{
    public const string PERMISSIONS = 'permissions';

    public const string ROLES = 'roles';

    public const string MODEL_HAS_PERMISSIONS = 'model_has_permissions';

    public const string MODEL_HAS_ROLES = 'model_has_roles';

    public const string ROLE_HAS_PERMISSIONS = 'role_has_permissions';

    public const string PERMISSION_DENIALS = 'permission_denials';

    public const string ORGANIZATION_MEMBERS = 'organization_members';

    public const string TEAM_FOREIGN_KEY = 'organization_id';

    public const string PERMISSION_FOREIGN_KEY = 'permission_id';

    public const string ROLE_FOREIGN_KEY = 'role_id';

    public const string MODEL_MORPH = 'model';

    public const string MODEL_TYPE = 'model_type';

    public const string MODEL_ID = 'model_id';

    public const string GUARD_NAME = 'guard_name';

    /**
     * Tabele w kolejności tworzenia (zależności kluczy obcych); usuwane w odwrotnej.
     *
     * @var list<string>
     */
    public const array ALL = [
        self::PERMISSIONS,
        self::ROLES,
        self::MODEL_HAS_PERMISSIONS,
        self::MODEL_HAS_ROLES,
        self::ROLE_HAS_PERMISSIONS,
        self::PERMISSION_DENIALS,
    ];
}
