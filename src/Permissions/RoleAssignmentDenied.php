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

use RuntimeException;

/**
 * Nadanie lub odebranie roli odrzucone: inna strefa, brak członkostwa w organizacji albo rola szersza niż
 * uprawnienia nadającego (eskalacja).
 */
final class RoleAssignmentDenied extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self("Role assignment denied: {$reason}");
    }
}
