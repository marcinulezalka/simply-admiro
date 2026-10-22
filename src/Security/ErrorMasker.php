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

namespace Simplysmart\Mainboard\Security;

use Illuminate\Config\Repository;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Czy odpowiedź o danym statusie ma wyglądać jak 404 (`mainboard.security.masked_statuses`): w strefie według
 * jej `security.mask_errors`, poza strefą i bez ustawienia strefy według `mainboard.security.mask_errors`
 * (null = tylko na produkcji). Odwiedzający nie odróżni wtedy braku dostępu od braku strony.
 */
final readonly class ErrorMasker
{
    public function __construct(
        private Repository $config,
        private CurrentZone $zone,
        private bool $production,
    ) {}

    public function masks(int $status): bool
    {
        return $this->isEnabled() && in_array($status, $this->config->array('mainboard.security.masked_statuses'), true);
    }

    private function isEnabled(): bool
    {
        $zone = $this->zone->has() ? $this->zone->get()->security->maskErrors : null;
        $global = $this->config->get('mainboard.security.mask_errors');

        return $zone ?? (is_bool($global) ? $global : $this->production);
    }
}
