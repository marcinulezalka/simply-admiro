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
 * Zwalidowane role i uprawnienia jednej strefy z pliku JSON; uprawnienia ról są już spłaszczone
 * (dziedziczenie `inherits`, `*` i `except` rozwinięte), więc w bazie nie ma hierarchii.
 */
final readonly class ZoneRoleSet
{
    /**
     * @param list<string> $permissions
     * @param array<string, list<string>> $roles Rola → jej wszystkie uprawnienia.
     */
    public function __construct(
        public string $zone,
        public array $permissions,
        public array $roles,
    ) {}

    /**
     * @return list<string>
     */
    public function permissionsOf(string $role): array
    {
        return $this->roles[$role] ?? [];
    }
}
