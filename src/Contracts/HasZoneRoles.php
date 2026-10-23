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

namespace Simplysmart\Mainboard\Contracts;

use BackedEnum;

/**
 * Konto strefy z rolami i uprawnieniami (spatie) w bieżącej organizacji; odmowa wygrywa z uprawnieniem z ról.
 */
interface HasZoneRoles
{
    /**
     * Czy konto ma uprawnienie w guardzie strefy w bieżącej organizacji.
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist Gdy uprawnienia nie ma w guardzie.
     */
    public function hasPermissionTo(BackedEnum|string $permission, ?string $guardName = null): bool;

    /**
     * Zapomina wczytane role, uprawnienia i odmowy (np. po zmianie bieżącej organizacji).
     */
    public function forgetZonePermissions(): static;
}
