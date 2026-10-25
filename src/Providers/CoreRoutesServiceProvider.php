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

namespace Simplysmart\Mainboard\Providers;

use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Mainboard\Http\Middleware\SecurityHeaders;
use Simplysmart\Mainboard\Http\Middleware\SetLocale;
use Simplysmart\Mainboard\Localization\LocaleRouting;

/**
 * Trasy Core z `routes/web.php`: prefiks nazw `mainboard.`, prefiks {locale} jak w strefach, wspólna lokalizacja
 * wiązanie parametrów (po SetLocale, który zdejmuje {locale}) i nagłówki bezpieczeństwa (CSP z nonce). Bez sesji: trasy Core nie należą do żadnej strefy.
 */
final class CoreRoutesServiceProvider extends ServiceProvider
{
    public const string NAME_PREFIX = 'mainboard.';

    public function boot(Router $router, LocaleRouting $locales): void
    {
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        $router->group([
            'middleware' => [SetLocale::class, SubstituteBindings::class, SecurityHeaders::class],
            'prefix' => $locales->prefix(),
            'as' => self::NAME_PREFIX,
            'where' => $locales->where(),
        ], __DIR__ . '/../../routes/web.php');
    }
}
