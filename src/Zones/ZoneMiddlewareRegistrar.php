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

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Simplysmart\Admiro\Http\Middleware\BlockAiAgents;
use Simplysmart\Admiro\Http\Middleware\PreventRequestForgeryWithoutCookie;
use Simplysmart\Admiro\Http\Middleware\RecordHistoryActor;
use Simplysmart\Admiro\Http\Middleware\SecurityHeaders;
use Simplysmart\Admiro\Http\Middleware\SetLocale;
use Simplysmart\Admiro\Http\Middleware\UseCurrentTeam;
use Simplysmart\Admiro\Http\Middleware\UseZone;

/**
 * Rejestruje grupę middleware strefy (`zone.<nazwa>`), używaną zamiast grupy `web`.
 *
 * UseZone musi poprzedzać StartSession, a SetLocale — SubstituteBindings (zdejmuje parametr {locale}).
 * BlockAiAgents stoi tuż po UseZone, więc zablokowany agent nie dostaje sesji.
 * RecordHistoryActor i UseCurrentTeam potrzebują sesji (konto z guarda strefy), więc stoją po StartSession;
 * UseCurrentTeam przed SubstituteBindings, bo wiązania tras zawęża bieżąca organizacja.
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
            BlockAiAgents::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgeryWithoutCookie::class,
            SetLocale::class,
            RecordHistoryActor::class,
            UseCurrentTeam::class,
            SubstituteBindings::class,
            SecurityHeaders::class,
        ]);
    }
}
