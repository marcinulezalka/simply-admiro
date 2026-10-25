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

namespace Simplysmart\Mainboard\Auth\Events;

/**
 * Zaufane urządzenia konta przestały być zaufane (ręcznie z panelu albo automatycznie).
 */
final readonly class TrustedDevicesRevoked
{
    public function __construct(
        public string $zone,
        public int|string $accountId,
        public int $count,
        public string $cause,
    ) {}
}
