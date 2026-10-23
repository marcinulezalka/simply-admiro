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

use Simplysmart\Mainboard\View\Navigation\NavigationGroup;
use Simplysmart\Mainboard\View\Navigation\SearchBox;

/**
 * Menu strefy w layoucie aplikacji (x-mainboard::zone.layout): pozycje według uprawnień zalogowanego konta, globalna
 * wyszukiwarka i druga linia pod nazwą konta. Implementacja należy do paczki strefy i jest rejestrowana w
 * Zones\ZoneNavigations; rozwiązywana dla żądania (może korzystać z kontekstu żądania i konta).
 */
interface ZoneNavigation
{
    /**
     * Grupy menu z bieżącą pozycją oznaczoną (`current`).
     *
     * @return list<NavigationGroup>
     */
    public function groups(): array;

    /**
     * Globalna wyszukiwarka w top barze albo null (konto bez uprawnienia do wyszukiwania).
     */
    public function search(): ?SearchBox;

    /**
     * Druga linia pod nazwą konta (np. organizacja i rola) albo null — wtedy nazwa bieżącej organizacji.
     */
    public function subtitle(): ?string;
}
