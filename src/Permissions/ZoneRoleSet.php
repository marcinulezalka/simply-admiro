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

namespace Simplysmart\Admiro\Permissions;

/**
 * Zwalidowane role i uprawnienia jednej strefy z pliku JSON; uprawnienia ról są już spłaszczone
 * (dziedziczenie `inherits`, `*` i `except` rozwinięte), więc w bazie nie ma hierarchii. `assigns` to role
 * delegowane posiadaczowi roli (RoleDelegation).
 */
final readonly class ZoneRoleSet
{
    /**
     * @param list<string> $permissions
     * @param array<string, list<string>> $roles Rola → jej wszystkie uprawnienia.
     * @param array<string, list<string>> $assigns Rola → role, które jej posiadacz nadaje i odbiera.
     */
    public function __construct(
        public string $zone,
        public array $permissions,
        public array $roles,
        public array $assigns = [],
    ) {}

    /**
     * @return list<string>
     */
    public function permissionsOf(string $role): array
    {
        return $this->roles[$role] ?? [];
    }

    /**
     * Role, które posiadacz roli nadaje i odbiera mimo braku ich uprawnień.
     *
     * @return list<string>
     */
    public function assignableBy(string $role): array
    {
        return $this->assigns[$role] ?? [];
    }
}
