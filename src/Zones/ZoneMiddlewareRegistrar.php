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

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Simplysmart\Mainboard\Http\Middleware\PreventRequestForgeryWithoutCookie;
use Simplysmart\Mainboard\Http\Middleware\SecurityHeaders;
use Simplysmart\Mainboard\Http\Middleware\SetLocale;
use Simplysmart\Mainboard\Http\Middleware\UseZone;

/**
 * Rejestruje grupę middleware strefy (`zone.<nazwa>`), używaną zamiast grupy `web`.
 *
 * UseZone musi poprzedzać StartSession, a SetLocale — SubstituteBindings (zdejmuje parametr {locale}).
 */
final readonly class ZoneMiddlewareRegistrar
{
    private const string GROUP_PREFIX = 'zone.';

    public function __construct(
        private Router $router,
    ) {}

    /**
     * Nazwa grupy middleware strefy.
     */
    public static function group(ZoneDefinition $zone): string
    {
        return self::GROUP_PREFIX . $zone->name;
    }

    public function register(ZoneDefinition $zone): void
    {
        $this->router->middlewareGroup(self::group($zone), [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            UseZone::class . ':' . $zone->name,
            StartSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgeryWithoutCookie::class,
            SetLocale::class,
            SubstituteBindings::class,
            SecurityHeaders::class,
        ]);
    }
}
