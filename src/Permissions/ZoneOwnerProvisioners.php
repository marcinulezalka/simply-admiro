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

namespace Simplysmart\Admiro\Permissions;

use Closure;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Simplysmart\Admiro\Contracts\ZoneOwnerProvisioner;

/**
 * Rejestr zakładania właścicieli stref; paczka strefy dodaje swój w providerze, najczęściej
 * `addDefault('<strefa>')` (DefaultOwnerProvisioner). Przechowuje fabryki, a provisioner powstaje dopiero przy
 * użyciu: w boot() providera zależności innych paczek (np. singleton rejestru spatie) mogą jeszcze nie istnieć.
 */
final class ZoneOwnerProvisioners
{
    /**
     * @var array<string, Closure(): ZoneOwnerProvisioner>
     */
    private array $factories = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param Closure(): ZoneOwnerProvisioner $factory
     */
    public function add(string $zone, Closure $factory): void
    {
        $this->factories[$zone] = $factory;
    }

    /**
     * Właściciel strefy z kontem Core (DefaultOwnerProvisioner).
     */
    public function addDefault(string $zone): void
    {
        $this->add($zone, fn(): ZoneOwnerProvisioner => $this->container->make(DefaultOwnerProvisioner::class, ['zone' => $zone]));
    }

    /**
     * @throws InvalidArgumentException Gdy strefa nie zakłada właścicieli.
     */
    public function for(string $zone): ZoneOwnerProvisioner
    {
        $factory = $this->factories[$zone] ?? throw new InvalidArgumentException("Zone [{$zone}] has no owner provisioner.");

        return $factory();
    }

    /**
     * Strefy, w których można założyć właściciela.
     *
     * @return list<string>
     */
    public function zones(): array
    {
        return array_keys($this->factories);
    }
}
