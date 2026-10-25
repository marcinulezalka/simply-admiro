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

namespace Simplysmart\Admiro\View\Navigation;

/**
 * Pozycja menu aplikacji (x-admiro::app.layout): etykieta, adres, ikona Core, stan bieżący i opcjonalny licznik.
 * Pozycja z podpozycjami (children) jest gałęzią: kliknięcie rozwija i zwija listę podpozycji połączonych liniami
 * drzewa (rozwinięta, gdy ona albo któraś podpozycja jest bieżąca); jej adres działa w menu zwiniętym do ikon.
 */
final readonly class NavigationItem
{
    /**
     * @param string $icon Nazwa ikony z `public/assets/img/icons/` (x-admiro::icon).
     * @param string|null $badge Krótka wartość obok etykiety, np. liczba oczekujących.
     * @param list<NavigationItem> $children Podpozycje gałęzi (bez ikon i bez dalszych poziomów).
     */
    public function __construct(
        public string $label,
        public string $url,
        public string $icon,
        public bool $current = false,
        public ?string $badge = null,
        public array $children = [],
    ) {}

    /**
     * Czy gałąź jest rozwinięta: pozycja albo któraś z jej podpozycji jest bieżąca.
     */
    public function isExpanded(): bool
    {
        if ($this->current) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child->current) {
                return true;
            }
        }

        return false;
    }
}
