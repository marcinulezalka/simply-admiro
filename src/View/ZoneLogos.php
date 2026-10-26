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
 * każdy plik raz (kilka stref jednej paczki dzieli zwykle to samo logo), w kolejności nazw stref, z adresem strony WWW
 * strefy (`website`; przy wspólnym logo pierwszy podany).
 */
final readonly class ZoneLogos
{
    public function __construct(
        private ZoneRegistry $zones,
    ) {}

    /**
     * @return list<array{svg: string, website: ?string}> Treść pliku SVG (fill="currentColor": przyjmuje kolor tekstu)
     *                                                   i strona WWW strefy (null = logo bez odnośnika).
     */
    public function all(): array
    {
        $logos = [];

        foreach ($this->zones->all() as $zone) {
            $path = $zone->logo === null ? false : realpath($zone->logo);

            if ($path === false) {
                continue;
            }

            $logos[$path] ??= ['svg' => (string) file_get_contents($path), 'website' => null];
            $logos[$path]['website'] ??= $zone->website;
        }

        return array_values($logos);
    }
}
