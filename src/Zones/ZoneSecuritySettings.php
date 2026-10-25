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

/**
 * Ustawienia bezpieczeństwa strefy (config strefy `security`).
 */
final readonly class ZoneSecuritySettings
{
    /**
     * @param bool|null $maskErrors Maskowanie błędów jako 404; null = `admiro.security.mask_errors`.
     * @param bool $blockAiAgents Agenci AI (`admiro.security.ai_agents`) dostają 403.
     */
    public function __construct(
        public ?bool $maskErrors = null,
        public bool $blockAiAgents = false,
    ) {}
}
