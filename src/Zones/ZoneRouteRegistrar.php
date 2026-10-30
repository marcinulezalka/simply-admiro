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

use Illuminate\Routing\Router;
use InvalidArgumentException;
use LogicException;
use Simplysmart\Admiro\Enums\FaviconType;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Http\Controllers\ZoneFaviconController;
use Simplysmart\Admiro\Http\Controllers\ZoneFontController;
use Simplysmart\Admiro\Http\SignInRoutes;
use Simplysmart\Admiro\Localization\LocaleRouting;

/**
 * Rejestruje trasy strefy: prefiks URL ({locale} i slug), prefiks nazw `<strefa>.`, grupę middleware
 * strefy oraz middleware poziomu dostępu każdego pliku tras.
 *
 * Pliki tras strefy definiują ścieżki względne i krótkie nazwy; brakujący plik jest pomijany.
 * Strefa z `favicon` dostaje trasę jej plików (`<strefa>.favicon`, dla każdego poziomu dostępu), strefa z `fonts`
 * trasę plików fontów (`<strefa>.font`).
 * Strefa z logowaniem dostaje też trasy logowania (Http\SignInRoutes) i konta (Http\AccountRoutes) z Core.
 * W strefie z `context_in_url` trasy dla zalogowanych z plików strefy mają przed ścieżką klucz kontekstu
 * (`{context}`, Zones\ContextKeys); trasy logowania i konta Core są bez klucza.
 */
final readonly class ZoneRouteRegistrar
{
    /** Plik ikony strony strefy (nazwa względem prefiksu strefy), gdy strefa ma `favicon`. */
    public const string FAVICON = 'favicon';

    /** Plik fontu strefy (nazwa względem prefiksu strefy), gdy strefa ma `fonts`. */
    public const string FONT = 'font';

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
        private AccountRoutes $account,
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
            'prefix' => $this->locales->prefix($zone->slug),
            'as' => $zone->routeNamePrefix . '.',
            'where' => $this->locales->where(),
        ];

        $this->router->group($attributes, function () use ($zone): void {
            if ($zone->favicon !== null) {
                $this->router->get('favicon/{type}', ZoneFaviconController::class)->where('type', implode('|', array_map(static fn(FaviconType $type): string => $type->value, $zone->favicon->types())))->name(self::FAVICON);
            }

            if ($zone->fonts !== null) {
                $this->router->get('font/{font}', ZoneFontController::class)->whereIn('font', $zone->fonts->keys())->name(self::FONT);
            }

            if ($zone->requiresLogin()) {
                $this->signIn->register($zone);
                $this->account->register($zone);
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

        $attributes = ['middleware' => $tier->middleware($zone)];

        if ($zone->contextInUrl && $tier->requiresLogin()) {
            $attributes['prefix'] = '{' . ContextKeys::PARAMETER . '}';
            $attributes['where'] = [ContextKeys::PARAMETER => ContextKeys::PATTERN];
        }

        $this->router->group($attributes, $path);
    }
}
