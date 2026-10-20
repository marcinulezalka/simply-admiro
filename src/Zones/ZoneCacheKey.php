<?php

declare(strict_types=1);

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
