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

use Illuminate\Contracts\Config\Repository;

/**
 * Przełącza config sesji na strefę (przed StartSession) i przywraca go po odpowiedzi.
 *
 * Bazowy config/session.php zapamiętuje raz, przy tworzeniu, więc kolejna strefa w tym samym
 * procesie (testy, Octane) zawsze startuje od wartości aplikacji, a nie od poprzedniej strefy.
 */
final readonly class ZoneSessionConfigurator
{
    /**
     * @param array<array-key, mixed> $baseSessionConfig
     * @param bool $forceSecure Ciasteczko tylko przez HTTPS (każde środowisko poza local).
     */
    public function __construct(
        private Repository $config,
        private ZoneSessionManager $session,
        private array $baseSessionConfig,
        private bool $forceSecure,
    ) {}

    /**
     * Ustawia sesję strefy i jej własny magazyn (tworzony z tego configu przy pierwszym użyciu).
     */
    public function apply(ZoneDefinition $zone): void
    {
        $settings = array_replace($this->baseSessionConfig, $zone->session->toSessionConfig());

        if ($this->forceSecure) {
            $settings['secure'] = true;
        }

        $this->config->set('session', $settings);
        $this->session->useZone($zone->name);
    }

    /**
     * Przywraca sesję i magazyn aplikacji.
     */
    public function restore(): void
    {
        $this->config->set('session', $this->baseSessionConfig);
        $this->session->useZone(null);
    }
}
