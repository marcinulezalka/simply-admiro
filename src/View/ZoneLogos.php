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
use Simplysmart\Admiro\Enums\AppBackdrop;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Logo zarejestrowanych stref (`logo` strefy, domyślnie `auth.logo`) do osadzenia inline, np. na stronie startowej:
 * każdy plik raz (kilka stref jednej paczki dzieli zwykle to samo logo), w kolejności nazw stref, z adresem strony WWW
 * strefy (`website`; przy wspólnym logo pierwszy podany), oraz logo w tle płótna strefy z `backdrop` = zone-logo.
 */
final readonly class ZoneLogos
{
    public function __construct(
        private ZoneRegistry $zones,
    ) {}

    /**
     * Treść pliku SVG logo strefy (fill="currentColor") do tła płótna layoutu aplikacji albo null, gdy tło strefy
     * to logotyp simplysmart (Enums\AppBackdrop).
     */
    public function backdrop(ZoneDefinition $zone): ?string
    {
        $path = $zone->backdrop === AppBackdrop::ZoneLogo ? $this->path($zone) : null;

        return $path === null ? null : (string) file_get_contents($path);
    }

    /**
     * @return list<array{svg: string, website: ?string}> Treść pliku SVG (fill="currentColor": przyjmuje kolor tekstu)
     *                                                   i strona WWW strefy (null = logo bez odnośnika).
     */
    public function all(): array
    {
        $logos = [];

        foreach ($this->zones->all() as $zone) {
            $path = $this->path($zone);

            if ($path === null) {
                continue;
            }

            $logos[$path] ??= ['svg' => (string) file_get_contents($path), 'website' => null];
            $logos[$path]['website'] ??= $zone->website;
        }

        return array_values($logos);
    }

    /**
     * Rzeczywista ścieżka pliku logo strefy albo null, gdy strefa nie ma logo.
     */
    private function path(ZoneDefinition $zone): ?string
    {
        $path = $zone->logo === null ? false : realpath($zone->logo);

        return $path === false ? null : $path;
    }
}
