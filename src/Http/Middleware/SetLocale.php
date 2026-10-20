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

namespace Simplysmart\Mainboard\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Localization\LocaleRouting;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wspólny middleware lokalizacji: język z parametru {locale} (albo domyślny bez prefiksu),
 * ustawiony w aplikacji, Carbonie i domyślnych parametrach URL; parametr jest zdejmowany
 * z trasy, więc kontrolery go nie dostają. Ścieżki żądania nie zmienia.
 */
final readonly class SetLocale
{
    public function __construct(
        private Application $app,
        private UrlGenerator $url,
        private LocaleRouting $locales,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $requested = $route->parameter(LocaleRouting::PARAMETER);
        $locale = is_string($requested) && $this->locales->isEnabled($requested) ? $requested : $this->locales->default();

        $this->app->setLocale($locale);
        Carbon::setLocale($locale);

        if ($this->locales->isPrefixed()) {
            $this->url->defaults([LocaleRouting::PARAMETER => $locale]);
        }

        $route->forgetParameter(LocaleRouting::PARAMETER);

        return $next($request);
    }
}
