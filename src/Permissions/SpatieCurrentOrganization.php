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

use Closure;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Spatie\Permission\PermissionRegistrar;

/**
 * Bieżąca organizacja przechowywana jako zespół spatie (jedno źródło prawdy dla sprawdzeń uprawnień).
 */
final readonly class SpatieCurrentOrganization implements CurrentOrganization
{
    public function __construct(
        private PermissionRegistrar $registrar,
    ) {}

    public function id(): ?int
    {
        $id = $this->registrar->getPermissionsTeamId();

        return is_numeric($id) ? (int) $id : null;
    }

    public function set(?int $organizationId): void
    {
        $this->registrar->setPermissionsTeamId($organizationId);
    }

    public function within(?int $organizationId, Closure $callback): mixed
    {
        $previous = $this->id();
        $this->set($organizationId);

        try {
            return $callback();
        } finally {
            $this->set($previous);
        }
    }
}
