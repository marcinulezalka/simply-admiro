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

namespace Simplysmart\Mainboard\Permissions;

use InvalidArgumentException;
use Simplysmart\Mainboard\Contracts\ZoneOwnerProvisioner;

/**
 * Rejestr kontraktów zakładania właścicieli; paczka strefy dodaje swój w providerze
 * (najczęściej `DefaultOwnerProvisioner` dla swojej strefy).
 */
final class ZoneOwnerProvisioners
{
    /**
     * @var array<string, ZoneOwnerProvisioner>
     */
    private array $provisioners = [];

    public function add(ZoneOwnerProvisioner $provisioner): void
    {
        $this->provisioners[$provisioner->zone()] = $provisioner;
    }

    /**
     * @throws InvalidArgumentException Gdy strefa nie zakłada właścicieli.
     */
    public function for(string $zone): ZoneOwnerProvisioner
    {
        return $this->provisioners[$zone] ?? throw new InvalidArgumentException("Zone [{$zone}] has no owner provisioner.");
    }

    /**
     * Strefy, w których można założyć właściciela.
     *
     * @return list<string>
     */
    public function zones(): array
    {
        return array_keys($this->provisioners);
    }
}
