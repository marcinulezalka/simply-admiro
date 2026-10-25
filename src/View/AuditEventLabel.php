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

namespace Simplysmart\Mainboard\View;

use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Czytelna nazwa zdarzenia dziennika (`audit.events.<zdarzenie>`, spacje i myślniki jako `_`): najpierw teksty paczki
 * strefy (jej własne zdarzenia), potem Core; bez tekstu — nazwa zdarzenia z dziennika.
 */
final class AuditEventLabel
{
    public static function of(string $event, ?ZoneDefinition $zone = null): string
    {
        $key = 'audit.events.' . str_replace([' ', '-'], '_', $event);
        $namespaced = $zone?->textKey($key) ?? $key;
        $label = text($namespaced);

        return $label === $namespaced ? $event : $label;
    }
}
