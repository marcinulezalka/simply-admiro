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

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Przekierowania middleware auth/guest liczone z bieżącej strefy (nie ma globalnej trasy `login`) i adres „Wstecz”
 * w nagłówku stron strefy — rodzic strony w hierarchii adresów (jak w okruszkach), nie historia przeglądarki.
 */
final readonly class ZoneRedirects
{
    public const string LOGIN_ROUTE = 'auth.login';

    public const string HOME_ROUTE = 'dashboard';

    public function __construct(
        private CurrentZone $zone,
        private Router $router,
        private UrlGenerator $url,
    ) {}

    /**
     * Logowanie strefy dla niezalogowanego; bez niego 404 (nie zdradza istnienia trasy).
     *
     * @throws NotFoundHttpException
     */
    public function guestRedirect(): string
    {
        $route = $this->route(self::LOGIN_ROUTE) ?? throw new NotFoundHttpException();

        return $this->url->route($route);
    }

    /**
     * Dashboard strefy dla zalogowanego, który wszedł na trasę tylko dla gości.
     */
    public function userRedirect(): string
    {
        $route = $this->route(self::HOME_ROUTE);

        return $route !== null ? $this->url->route($route) : $this->url->to('/');
    }

    /**
     * Adres „Wstecz” w nagłówku strony: najbliższa strona nadrzędna w hierarchii adresów (jak w okruszkach) — adres
     * bez ostatnich segmentów ścieżki, pod którym jest strona GET tej strefy (np. `/onboarding/languages` →
     * `/onboarding`, `/organizations/{token}/edit` → `/organizations`), a bez takiej — dashboard strefy; null na samym
     * dashboardzie (strona zerowego poziomu) i poza strefą. Strona nadrzędna nie jest płytsza niż dashboard (np. przy
     * dashboardzie `/<slug>/panel` strona publiczna `/<slug>` nie jest rodzicem `/<slug>/account/profile`). Nie zależy
     * od poprzedniej strony, więc się nie zapętla.
     */
    public function backUrl(): ?string
    {
        $home = $this->route(self::HOME_ROUTE);

        if ($home === null) {
            return null;
        }

        $homeUrl = $this->url->route($home);
        $current = rtrim($this->url->current(), '/');

        if ($current === rtrim($homeUrl, '/')) {
            return null;
        }

        $root = rtrim($this->url->to('/'), '/');
        $homeDepth = self::depth($homeUrl);
        $candidate = $current;

        while (($slash = strrpos($candidate, '/')) !== false && strlen($candidate = substr($candidate, 0, $slash)) > strlen($root) && self::depth($candidate) >= $homeDepth) {
            if ($candidate === rtrim($homeUrl, '/') || $this->isZonePage($candidate)) {
                return $candidate;
            }
        }

        return $homeUrl;
    }

    /**
     * Liczba segmentów ścieżki adresu (`/shop/panel` → 2).
     */
    private static function depth(string $url): int
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        return $path === '' ? 0 : substr_count($path, '/') + 1;
    }

    /**
     * Czy pod adresem jest strona GET bieżącej strefy (nazwana trasa z jej prefiksem, poza logowaniem).
     */
    private function isZonePage(string $url): bool
    {
        try {
            $name = $this->router->getRoutes()->match(Request::create($url))->getName();
        } catch (HttpException) {
            return false;
        }

        $zone = $this->zone->get();

        return $name !== null && str_starts_with($name, $zone->routeName('')) && ! str_starts_with($name, $zone->routeName('auth.'));
    }

    private function route(string $name): ?string
    {
        if (! $this->zone->has()) {
            return null;
        }

        $route = $this->zone->get()->routeName($name);

        return $this->router->has($route) ? $route : null;
    }
}
