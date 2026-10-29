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
 * Grupa pozycji menu aplikacji z opcjonalnym nagłówkiem (bez nagłówka: pierwsza, główna grupa).
 */
final readonly class NavigationGroup
{
    /**
     * @param list<NavigationItem|NavigationForm> $items Pozycje: linki albo formularze POST (np. przełączenie organizacji).
     * @param ?string $icon Ikona przed nazwą grupy (np. rodzaj organizacji w przełączniku kontekstu).
     * @param ?string $iconLabel Znaczenie ikony dla czytników ekranu i w podpowiedzi (np. „Departament”).
     */
    public function __construct(
        public ?string $label,
        public array $items,
        public ?string $icon = null,
        public ?string $iconLabel = null,
    ) {}
}
