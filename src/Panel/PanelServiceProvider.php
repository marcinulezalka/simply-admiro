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
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Zones\ZoneDefinitionFactory;
use Simplysmart\Admiro\Zones\ZoneGuardRegistrar;
use Simplysmart\Admiro\Zones\ZoneMiddlewareRegistrar;
use Simplysmart\Admiro\Zones\ZoneRouteRegistrar;
use Simplysmart\Admiro\Zones\ZoneServiceProvider;
use Simplysmart\Admiro\Panel\Http\AdmiroNavigation;
use Simplysmart\Admiro\Translation\CascadeTextTranslator;
use Simplysmart\Admiro\Zones\ZoneNavigations;

/**
 * Strefa administratorów na mechanizmie stref: rejestracja strefy (config `admiro.zone`), trasy, menu i komenda
 * zakładania kont. Config, widoki (`admiro::panel.*`) i teksty (`admiro::...`) ładuje provider rdzenia.
 */
final class PanelServiceProvider extends ZoneServiceProvider
{
    /**
     * Namespace tekstów strefy (`text('admiro::...')`, `sign_in.admiro.*`): wspólny z rdzeniem.
     */
    public const string NAMESPACE = CascadeTextTranslator::CORE_NAMESPACE;

    public function boot(
        ZoneDefinitionFactory $factory,
        ZoneRegistry $zones,
        ZoneGuardRegistrar $guards,
        ZoneMiddlewareRegistrar $middleware,
        ZoneRouteRegistrar $routes,
    ): void {
        parent::boot($factory, $zones, $guards, $middleware, $routes);

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
