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

use Illuminate\Contracts\Container\Container;
use Simplysmart\Admiro\Contracts\ZoneNavigation;

/**
 * Rejestr menu stref (singleton): paczka strefy dodaje klasę ZoneNavigation dla swojej strefy w providerze, layout
 * strefy rozwiązuje ją dla bieżącego żądania. Strefa bez menu dostaje layout bez pozycji menu.
 */
final class ZoneNavigations
{
    /**
     * @var array<string, class-string<ZoneNavigation>>
     */
    private array $navigations = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param class-string<ZoneNavigation> $navigation
     */
    public function add(string $zone, string $navigation): void
    {
        $this->navigations[$zone] = $navigation;
    }

    public function for(string $zone): ?ZoneNavigation
    {
        $navigation = $this->navigations[$zone] ?? null;

        return $navigation === null ? null : $this->container->make($navigation);
    }
}
