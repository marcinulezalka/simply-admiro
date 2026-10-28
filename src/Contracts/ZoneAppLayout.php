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

namespace Simplysmart\Admiro\Contracts;

use Simplysmart\Admiro\Enums\AppLayout;

/**
 * Opcjonalne rozszerzenie Contracts\ZoneNavigation: wariant layoutu aplikacji dla bieżącego żądania zamiast
 * `app_layout` strefy (np. Workspace tylko w kontekście jednej roli konta). Layout Workspace bierze wtedy mapę strony
 * i skróty z Contracts\ZoneSitemap i Contracts\ZoneShortcuts, gdy menu strefy je implementuje.
 */
interface ZoneAppLayout
{
    /**
     * Wariant layoutu w tym żądaniu albo null (wariant strefy, `app_layout`).
     */
    public function appLayout(): ?AppLayout;
}
