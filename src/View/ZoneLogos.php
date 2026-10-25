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

use Simplysmart\Admiro\Contracts\ZoneRegistry;

/**
 * Logo zarejestrowanych stref (`logo` strefy, domyślnie `auth.logo`) do osadzenia inline, np. na stronie startowej:
 * każdy plik raz (kilka stref jednej paczki dzieli zwykle to samo logo), w kolejności nazw stref.
 */
final readonly class ZoneLogos
{
    public function __construct(
        private ZoneRegistry $zones,
    ) {}

    /**
     * @return list<string> Treść plików SVG (fill="currentColor": przyjmują kolor tekstu).
     */
    public function svgs(): array
    {
        $logos = [];

        foreach ($this->zones->all() as $zone) {
            $path = $zone->logo === null ? false : realpath($zone->logo);

            if ($path !== false && ! isset($logos[$path])) {
                $logos[$path] = (string) file_get_contents($path);
            }
        }

        return array_values($logos);
    }
}
