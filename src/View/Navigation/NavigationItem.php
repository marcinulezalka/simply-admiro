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
 * Pozycja menu aplikacji (x-mainboard::app.layout): etykieta, adres, ikona Core, stan bieżący i opcjonalny licznik.
 */
final readonly class NavigationItem
{
    /**
     * @param string $icon Nazwa ikony z `public/assets/img/icons/` (x-mainboard::icon).
     * @param string|null $badge Krótka wartość obok etykiety, np. liczba oczekujących.
     */
    public function __construct(
        public string $label,
        public string $url,
        public string $icon,
        public bool $current = false,
        public ?string $badge = null,
    ) {}
}
