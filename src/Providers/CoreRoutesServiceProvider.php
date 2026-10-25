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

namespace Simplysmart\Admiro\Providers;

use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Admiro\Http\Middleware\SecurityHeaders;
use Simplysmart\Admiro\Http\Middleware\SetLocale;
use Simplysmart\Admiro\Localization\LocaleRouting;

/**
 * Trasy Core z prefiksem {locale} jak w strefach, wspólną lokalizacją, wiązaniem parametrów (po SetLocale, który
 * zdejmuje {locale}) i nagłówkami bezpieczeństwa (CSP z nonce). Bez sesji: trasy Core nie należą do żadnej strefy.
 * `routes/web.php` (polityki, zgoda na cookies) ma prefiks nazw `admiro.`, wspólny ze strefą administratorów, więc
 * nazwy tras strefy nie mogą powtarzać nazw stąd; `routes/design-system.php` ma prefiks `simplysmart.`.
 */
final class CoreRoutesServiceProvider extends ServiceProvider
{
    public const string NAME_PREFIX = 'admiro.';

    public const string DESIGN_SYSTEM_NAME_PREFIX = 'simplysmart.';

    /**
     * Pliki tras Core z ich prefiksami nazw.
     *
     * @var array<string, string>
     */
    private const array ROUTE_FILES = [
        'web.php' => self::NAME_PREFIX,
        'design-system.php' => self::DESIGN_SYSTEM_NAME_PREFIX,
    ];

    public function boot(Router $router, LocaleRouting $locales): void
    {
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        foreach (self::ROUTE_FILES as $file => $namePrefix) {
            $router->group([
                'middleware' => [SetLocale::class, SubstituteBindings::class, SecurityHeaders::class],
                'prefix' => $locales->prefix(),
                'as' => $namePrefix,
                'where' => $locales->where(),
            ], __DIR__ . '/../../routes/' . $file);
        }
    }
}
