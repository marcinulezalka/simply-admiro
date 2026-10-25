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

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Contracts\Session\Session;
use Illuminate\Session\SessionManager;
use LogicException;

/**
 * Menedżer sesji z osobnym magazynem dla każdej strefy: magazyn strefy powstaje z configu sesji
 * tej strefy (ciasteczko, tabela, szyfrowanie) i nie miesza się z magazynem aplikacji ani innej strefy.
 */
final class ZoneSessionManager extends SessionManager
{
    private ?string $zone = null;

    /**
     * Strefa, której magazyn zwraca driver(); null = magazyn aplikacji.
     */
    public function useZone(?string $zone): void
    {
        $this->zone = $zone;
    }

    /**
     * @param string|null $driver
     */
    public function driver($driver = null): Session
    {
        $driver ??= $this->getDefaultDriver() ?? throw new LogicException('No session driver is configured.');
        $key = $this->zone === null ? $driver : $driver . '@' . $this->zone;
        $store = $this->drivers[$key] ??= $this->createDriver($driver);

        return $store instanceof Session ? $store : throw new LogicException("Session driver [{$driver}] did not build a session store.");
    }
}
