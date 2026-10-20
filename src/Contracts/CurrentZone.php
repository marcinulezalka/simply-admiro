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

namespace Simplysmart\Mainboard\Contracts;

use LogicException;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Strefa bieżącego żądania, ustawiana przez middleware UseZone i czyszczona po odpowiedzi.
 *
 * Wiązanie jest `scoped`, więc stan nie przechodzi między żądaniami (Octane, kolejka).
 */
interface CurrentZone
{
    /**
     * Ustawia strefę bieżącego żądania.
     */
    public function set(ZoneDefinition $zone): void;

    /**
     * Usuwa strefę (koniec żądania).
     */
    public function forget(): void;

    /**
     * Czy żądanie należy do strefy.
     */
    public function has(): bool;

    /**
     * Strefa bieżącego żądania.
     *
     * @throws LogicException Gdy żądanie nie przeszło przez UseZone.
     */
    public function get(): ZoneDefinition;
}
