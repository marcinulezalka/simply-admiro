<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Contracts\Config\Repository;

/**
 * Dopisuje do config/auth guard sesyjny i provider Eloquent strefy z logowaniem
 * (bez zmian w pliku config/auth.php aplikacji).
 */
final readonly class ZoneGuardRegistrar
{
    public function __construct(
        private Repository $config,
    ) {}

    public function register(ZoneDefinition $zone): void
    {
        if ($zone->model === null) {
            return;
        }

        $this->config->set([
            "auth.guards.{$zone->guard}" => ['driver' => 'session', 'provider' => $zone->provider],
            "auth.providers.{$zone->provider}" => ['driver' => 'eloquent', 'model' => $zone->model],
        ]);
    }
}
