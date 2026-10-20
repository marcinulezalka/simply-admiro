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

use Illuminate\Routing\Router;
use InvalidArgumentException;
use LogicException;
use Simplysmart\Mainboard\Http\SignInRoutes;
use Simplysmart\Mainboard\Localization\LocaleRouting;

/**
 * Rejestruje trasy strefy: prefiks URL ({locale} i slug), prefiks nazw `<strefa>.`, grupę middleware
 * strefy oraz middleware poziomu dostępu każdego pliku tras.
 *
 * Pliki tras strefy definiują ścieżki względne i krótkie nazwy; brakujący plik jest pomijany.
 * Strefa z logowaniem dostaje też trasy logowania Core (Http\SignInRoutes).
 */
final readonly class ZoneRouteRegistrar
{
    /**
     * @var array<string, AccessTier>
     */
    private const array ROUTE_FILES = [
        'public.php' => AccessTier::Public,
        'account.php' => AccessTier::Authenticated,
        'zone.php' => AccessTier::Authenticated,
    ];

    public function __construct(
        private Router $router,
        private LocaleRouting $locales,
        private SignInRoutes $signIn,
    ) {}

    /**
     * @throws InvalidArgumentException Gdy slug strefy jest kodem włączonego języka.
     * @throws LogicException Gdy strefa bez logowania ma trasy dla zalogowanych.
     */
    public function register(ZoneDefinition $zone): void
    {
        if (in_array($zone->slug, $this->locales->enabled(), true)) {
            throw new InvalidArgumentException("Zone [{$zone->name}] slug [{$zone->slug}] must not be a locale code.");
        }

        $attributes = [
            'middleware' => [ZoneMiddlewareRegistrar::group($zone)],
            'prefix' => $this->prefix($zone),
            'as' => $zone->routeNamePrefix . '.',
        ];

        if ($this->locales->isPrefixed()) {
            $attributes['where'] = [LocaleRouting::PARAMETER => $this->locales->pattern()];
        }

        $this->router->group($attributes, function () use ($zone): void {
            if ($zone->requiresLogin()) {
                $this->signIn->register($zone);
            }

            foreach (self::ROUTE_FILES as $file => $tier) {
                $this->registerFile($zone, $zone->routesPath . '/' . $file, $tier);
            }
        });
    }

    private function registerFile(ZoneDefinition $zone, string $path, AccessTier $tier): void
    {
        if (! is_file($path)) {
            return;
        }

        if ($tier->requiresLogin() && ! $zone->requiresLogin()) {
            throw new LogicException("Zone [{$zone->name}] has no sign-in, so it cannot have [{$path}].");
        }

        $this->router->middleware($tier->middleware($zone))->group($path);
    }

    private function prefix(ZoneDefinition $zone): string
    {
        $segments = [$this->locales->isPrefixed() ? '{' . LocaleRouting::PARAMETER . '}' : '', $zone->slug];

        return implode('/', array_filter($segments, static fn(string $segment): bool => $segment !== ''));
    }
}
