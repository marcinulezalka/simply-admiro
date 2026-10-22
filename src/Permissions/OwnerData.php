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
 * Dane właściciela zakładanego w strefie.
 */
final readonly class OwnerData
{
    public function __construct(
        public string $name,
        #[\SensitiveParameter]
        public string $email,
    ) {}
}
