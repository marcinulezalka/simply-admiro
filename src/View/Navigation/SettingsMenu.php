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

namespace Simplysmart\Mainboard\View\Navigation;

/**
 * Menu ustawień w layoucie aplikacji: grupy ustawień (strefy, konto, wygląd), adres wejścia (pierwsza pozycja), czy
 * bieżąca strona jest ustawieniem (wtedy menu boczne pokazuje ustawienia zamiast menu aplikacji) i adres powrotu do
 * aplikacji.
 */
final readonly class SettingsMenu
{
    /**
     * @param list<NavigationGroup> $groups
     */
    public function __construct(
        public array $groups,
        public string $url,
        public bool $active,
        public string $backUrl,
    ) {}
}
