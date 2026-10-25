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

namespace Simplysmart\Mainboard\Zones;

use InvalidArgumentException;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;

/**
 * Rejestr stref w pamięci procesu (singleton), wypełniany przez providery paczek stref.
 */
final class InMemoryZoneRegistry implements ZoneRegistry
{
    /**
     * @var array<string, ZoneDefinition>
     */
    private array $zones = [];

    public function register(ZoneDefinition $zone): void
    {
        foreach ($this->zones as $existing) {
            $this->assertDistinct($existing, $zone);
        }

        $this->zones[$zone->name] = $zone;
    }

    public function get(string $name): ZoneDefinition
    {
        return $this->zones[$name] ?? throw new InvalidArgumentException("Zone [{$name}] is not registered.");
    }

    public function has(string $name): bool
    {
        return isset($this->zones[$name]);
    }

    public function all(): array
    {
        return $this->zones;
    }

    /**
     * Strefy nie mogą dzielić nazwy, guardu, prefiksu nazw tras ani slugu.
     */
    private function assertDistinct(ZoneDefinition $existing, ZoneDefinition $zone): void
    {
        $clashes = [
            'name' => $existing->name === $zone->name,
            'guard' => $zone->requiresLogin() && $existing->requiresLogin() && $existing->guard === $zone->guard,
            'route name prefix' => $existing->routeNamePrefix === $zone->routeNamePrefix,
            'slug' => $existing->slug === $zone->slug,
        ];

        foreach ($clashes as $attribute => $clash) {
            if ($clash) {
                throw new InvalidArgumentException("Zone [{$zone->name}] has the same {$attribute} as zone [{$existing->name}].");
            }
        }
    }
}
