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

namespace Simplysmart\Admiro\Panel;

use Simplysmart\Admiro\Panel\Console\CreateAdmiroAccount;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Zones\ZoneDefinitionFactory;
use Simplysmart\Mainboard\Zones\ZoneGuardRegistrar;
use Simplysmart\Mainboard\Zones\ZoneMiddlewareRegistrar;
use Simplysmart\Mainboard\Zones\ZoneRouteRegistrar;
use Simplysmart\Mainboard\Zones\ZoneServiceProvider;
use Simplysmart\Admiro\Panel\Http\AdmiroNavigation;
use Simplysmart\Mainboard\Zones\ZoneNavigations;

/**
 * Strefa administratorów: config strefy, trasy, widoki, tłumaczenia i komenda zakładania kont.
 */
final class PanelServiceProvider extends ZoneServiceProvider
{
    /**
     * Namespace widoków i tłumaczeń paczki (`view('admiro::panel....')`, `text('admiro::...')`).
     */
    public const string NAMESPACE = 'admiro';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/admiro.php', 'admiro');
    }

    public function boot(
        ZoneDefinitionFactory $factory,
        ZoneRegistry $zones,
        ZoneGuardRegistrar $guards,
        ZoneMiddlewareRegistrar $middleware,
        ZoneRouteRegistrar $routes,
    ): void {
        parent::boot($factory, $zones, $guards, $middleware, $routes);

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', self::NAMESPACE);
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', self::NAMESPACE);
        $this->app->make(ZoneNavigations::class)->add(config()->string('admiro.zone.name'), AdmiroNavigation::class);

        if ($this->app->runningInConsole()) {
            $this->commands([CreateAdmiroAccount::class]);
        }
    }

    protected function routeNamePrefix(): string
    {
        return 'admiro';
    }

    protected function zoneConfig(): array
    {
        return config()->array('admiro.zone');
    }

    protected function routesPath(): string
    {
        return __DIR__ . '/../../routes/panel';
    }
}
