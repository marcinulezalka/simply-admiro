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
 * Kontekst konta rozpoznany z klucza w adresie (Zones\ContextKeys::resolve()): organizacja i rola (null — konto bez
 * organizacji albo bez roli w niej) oraz czy klucz pochodzi z bieżącego `APP_KEY` (false — z `APP_PREVIOUS_KEYS`,
 * adres do przekierowania na aktualny klucz).
 */
final readonly class ContextMatch
{
    public function __construct(
        public ?int $organizationId,
        public ?string $role,
        public bool $current,
    ) {}
}
