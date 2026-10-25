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

use InvalidArgumentException;

/**
 * Plik ról i uprawnień strefy nie przeszedł walidacji (nic nie zostało zapisane).
 */
final class InvalidPermissionFile extends InvalidArgumentException
{
    public static function in(string $path, string $reason): self
    {
        return new self('Permission file [' . basename($path) . "]: {$reason}");
    }
}
