<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Router;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Przekierowania middleware auth/guest liczone z bieżącej strefy; nie ma globalnej trasy `login`.
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

    private function route(string $name): ?string
    {
        if (! $this->zone->has()) {
            return null;
        }

        $route = $this->zone->get()->routeName($name);

        return $this->router->has($route) ? $route : null;
    }
}
