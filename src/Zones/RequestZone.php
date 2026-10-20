<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Zones;

use LogicException;
use Simplysmart\Mainboard\Contracts\CurrentZone;

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
}
