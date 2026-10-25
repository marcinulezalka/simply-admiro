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

/**
 * Jedyne miejsce budowania kluczy cache strefy: `zone:<strefa>:<segment>:...`.
 */
final class ZoneCacheKey
{
    private const string PREFIX = 'zone';

    private const string SEPARATOR = ':';

    public static function make(ZoneDefinition|string $zone, string ...$segments): string
    {
        $name = $zone instanceof ZoneDefinition ? $zone->name : $zone;

        return implode(self::SEPARATOR, [self::PREFIX, $name, ...$segments]);
    }
}
