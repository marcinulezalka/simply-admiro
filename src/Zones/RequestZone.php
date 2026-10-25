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

namespace Simplysmart\Admiro\Zones;

use Closure;
use LogicException;
use Simplysmart\Admiro\Contracts\CurrentZone;

/**
 * Strefa bieżącego żądania (wiązanie `scoped`).
 */
final class RequestZone implements CurrentZone
{
    private ?ZoneDefinition $zone = null;

    public function set(ZoneDefinition $zone): void
    {
        $this->zone = $zone;
    }

    public function forget(): void
    {
        $this->zone = null;
    }

    public function has(): bool
    {
        return $this->zone !== null;
    }

    public function get(): ZoneDefinition
    {
        return $this->zone ?? throw new LogicException('The request has no zone: route it through the zone middleware group.');
    }

    public function within(ZoneDefinition $zone, Closure $callback): mixed
    {
        $previous = $this->zone;
        $this->zone = $zone;

        try {
            return $callback($zone);
        } finally {
            $this->zone = $previous;
        }
    }
}
