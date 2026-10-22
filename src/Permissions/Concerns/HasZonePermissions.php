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

namespace Simplysmart\Mainboard\Permissions\Concerns;

use BackedEnum;
use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Mainboard\Models\Permissions\PermissionDenial;
use Simplysmart\Mainboard\Models\Relations\HasPermissionDenials;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

/**
 * Role i uprawnienia spatie konta strefy z odmowami: odmowa w bieżącej organizacji wygrywa z uprawnieniem
 * z ról i bezpośrednim. Nadpisuje hasPermissionTo(), więc middleware `permission:`, Gate i can() uwzględniają odmowy.
 *
 * Odmowy konta są wczytywane raz na instancję modelu (relacja `permissionDenials`).
 *
 * @property-read Collection<int, PermissionDenial> $permissionDenials
 */
trait HasZonePermissions
{
    use HasPermissionDenials;
    use HasRoles {
        hasPermissionTo as private hasPermissionFromRoles;
    }

    /**
     * @param string|int|Permission|BackedEnum $permission
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     */
    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        if (! $this->hasPermissionFromRoles($permission, $guardName)) {
            return false;
        }

        return ! $this->isPermissionDenied($this->filterPermission($permission, $guardName));
    }

    /**
     * Czy konto ma odmowę uprawnienia w bieżącej organizacji.
     */
    public function isPermissionDenied(Permission $permission): bool
    {
        $organization = app(PermissionRegistrar::class)->getPermissionsTeamId();

        return $this->permissionDenials->contains(
            static fn(PermissionDenial $denial): bool => $denial->permission_id === $permission->getKey()
                && $denial->organization_id === $organization,
        );
    }
}
