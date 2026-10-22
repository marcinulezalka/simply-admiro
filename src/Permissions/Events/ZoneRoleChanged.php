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

namespace Simplysmart\Mainboard\Permissions\Events;

use Simplysmart\Mainboard\Auth\AccountActor;

/**
 * Rola konta strefy w organizacji została nadana (`assigned` = true) albo odebrana (dziennik `sign_in`).
 */
final readonly class ZoneRoleChanged
{
    public function __construct(
        public string $zone,
        public int|string $accountId,
        public string $role,
        public int $organizationId,
        public bool $assigned,
        public AccountActor $actor,
    ) {}
}
