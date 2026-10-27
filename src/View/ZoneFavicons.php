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

namespace Simplysmart\Admiro\View;

use Simplysmart\Admiro\Enums\FaviconType;
use Simplysmart\Admiro\Zones\ZoneDefinition;
use Simplysmart\Admiro\Zones\ZoneRouteRegistrar;

/**
 * Ikona strefy jako obraz (np. znak strefy w drzewie organizacji panelu): adres jej favicony (`favicon` strefy) w
 * najlepszym dostępnym formacie — SVG, potem PNG ekranu startowego, potem ICO; null, gdy strefa nie ma favicony.
 */
final class ZoneFavicons
{
    private const array PREFERRED = [FaviconType::Svg, FaviconType::Apple, FaviconType::Ico];

    public static function url(ZoneDefinition $zone): ?string
    {
        $favicon = $zone->favicon;

        if ($favicon === null) {
            return null;
        }

        foreach (self::PREFERRED as $type) {
            if (in_array($type, $favicon->types(), true)) {
                return route($zone->routeName(ZoneRouteRegistrar::FAVICON), ['type' => $type->value, 'v' => $favicon->version($type)]);
            }
        }

        return null;
    }
}
