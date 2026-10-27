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
use Illuminate\Routing\Router;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Przekierowania middleware auth/guest liczone z bieżącej strefy (nie ma globalnej trasy `login`) i adres „Wstecz”
 * w nagłówku stron strefy.
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
     * Adres „Wstecz” w nagłówku strony: poprzednia strona tej aplikacji (nie strona główna `/`), a gdy jej nie ma albo
     * to ta sama strona (np. po zapisie formularza) — dashboard strefy; null na samym dashboardzie bez innej poprzedniej
     * strony.
     */
    public function backUrl(): ?string
    {
        $current = $this->url->current();
        $root = rtrim($this->url->to('/'), '/');
        $home = $this->route(self::HOME_ROUTE);
        $candidates = [$this->url->previous(), $home !== null ? $this->url->route($home) : null];

        foreach ($candidates as $candidate) {
            $page = $candidate === null ? null : rtrim((string) strtok($candidate, '?#'), '/');

            if ($page !== null && str_starts_with($page . '/', $root . '/') && $page !== $root && $page !== rtrim($current, '/')) {
                return $candidate;
            }
        }

        return null;
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
