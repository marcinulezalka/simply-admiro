<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\ServiceProvider;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;

/**
 * Opcjonalna baza providera paczki strefy: buduje definicję z configu strefy i oddaje ją
 * registrarom Core (guard, grupa middleware, trasy).
 *
 * Wszystko dzieje się w boot(), bo providery paczek mogą rejestrować się przed Core. Paczka strefy
 * w register() łączy swój config (mergeConfigFrom), a tu podaje go przez zoneConfig().
 */
abstract class ZoneServiceProvider extends ServiceProvider
{
    /**
     * Stały prefiks nazw tras strefy (np. "alpha" → "alpha.dashboard"); nie pochodzi z configu,
     * więc zmiana slugu nie zmienia nazw tras.
     */
    abstract protected function routeNamePrefix(): string;

    /**
     * Config strefy (name, slug, guard, provider, model, session).
     *
     * @return array<array-key, mixed>
     */
    abstract protected function zoneConfig(): array;

    /**
     * Katalog plików tras strefy.
     */
    abstract protected function routesPath(): string;

    public function boot(
        ZoneDefinitionFactory $factory,
        ZoneRegistry $zones,
        ZoneGuardRegistrar $guards,
        ZoneMiddlewareRegistrar $middleware,
        ZoneRouteRegistrar $routes,
    ): void {
        $zone = $factory->make($this->zoneConfig(), $this->routeNamePrefix(), $this->routesPath());

        $zones->register($zone);
        $guards->register($zone);
        $middleware->register($zone);

        if (! ($this->app instanceof CachesRoutes && $this->app->routesAreCached())) {
            $routes->register($zone);
        }
    }
}
