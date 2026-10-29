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

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Cache\RateLimiter;
use Illuminate\Config\Repository;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Admiro\Console\PruneCookieConsents;
use Simplysmart\Admiro\Contracts\ConsentScripts;
use Simplysmart\Admiro\Contracts\CookieCatalog;
use Simplysmart\Admiro\Cookies\ConsentBanner;
use Simplysmart\Admiro\Cookies\CookieDescription;
use Simplysmart\Admiro\Cookies\InMemoryConsentScripts;
use Simplysmart\Admiro\Cookies\InMemoryCookieCatalog;
use Simplysmart\Admiro\Enums\CookieCategory;

/**
 * Cookies i zgody: katalog cookies i skrypty opcjonalne (singletony, paczki dopisują swoje), cookies Core w katalogu,
 * stan banera dla komponentów `cookie-consent`, `cookie-links` i menu konta layoutu aplikacji, limit zapisu zgód i codzienne czyszczenie rejestru.
 */
final class CookiesServiceProvider extends ServiceProvider
{
    public const string THROTTLE = 'admiro.cookie-consent';

    private const array VIEWS = ['admiro::components.cookie-consent', 'admiro::components.cookie-links', 'admiro::components.app.layout'];

    public function register(): void
    {
        $this->app->singletonIf(CookieCatalog::class, InMemoryCookieCatalog::class);
        $this->app->singletonIf(ConsentScripts::class, InMemoryConsentScripts::class);
    }

    public function boot(CookieCatalog $catalog, RateLimiter $limiter, Repository $config, ViewFactory $views): void
    {
        foreach ($this->coreCookies($config) as $cookie) {
            $catalog->add($cookie);
        }

        $limiter->for(self::THROTTLE, static fn(Request $request): Limit => Limit::perMinute($config->integer('admiro.cookies.throttle_per_minute'))->by((string) $request->ip()));
        $views->composer(self::VIEWS, static fn(View $view) => $view->with('cookieConsent', app(ConsentBanner::class)->state()));

        if ($this->app->runningInConsole()) {
            $this->commands([PruneCookieConsents::class]);
            $this->callAfterResolving(Schedule::class, static function (Schedule $schedule): void {
                $schedule->command(PruneCookieConsents::class)->daily();
            });
        }
    }

    /**
     * @return list<CookieDescription>
     */
    private function coreCookies(Repository $config): array
    {
        return [
            new CookieDescription('*_session', CookieCategory::Necessary, 'cookies.core.session', 'cookies.lifetimes.session'),
            new CookieDescription('*_device', CookieCategory::Necessary, 'cookies.core.device', 'cookies.lifetimes.device'),
            new CookieDescription($config->string('admiro.cookies.cookie_name'), CookieCategory::Necessary, 'cookies.core.consent', 'cookies.lifetimes.consent'),
            new CookieDescription('ss-theme, ss-palette, ss-text-size, ss-layout-width, ss-font, ss-sidebar-collapsed', CookieCategory::Necessary, 'cookies.core.appearance', 'cookies.lifetimes.storage'),
        ];
    }
}
