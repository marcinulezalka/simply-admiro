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

use Closure;
use LogicException;
use Simplysmart\Mainboard\View\DesignSystem\PreviewArea;
use Simplysmart\Mainboard\View\DesignSystem\PreviewView;

/**
 * Katalog gotowych widoków strony design systemu: Core rejestruje swoje, paczki stref dokładają własne obszary
 * i widoki w providerze (np. makiety ekranów strefy), bez zmian w Core. Nawigacja i trasa
 * `mainboard.design-system.view` czytają tylko katalog.
 */
interface DesignSystemCatalog
{
    /**
     * @param string $key Klucz obszaru (unikalny w aplikacji).
     * @param string $labelKey Klucz tekstu etykiety dla `text()`, np. `design_system.areas.workspace`.
     *
     * @throws LogicException Gdy obszar o tym kluczu już istnieje.
     */
    public function addArea(string $key, string $labelKey): void;

    /**
     * @param string $area Klucz istniejącego obszaru.
     * @param string $key Klucz widoku w adresie `/design-system/views/{key}` (unikalny w aplikacji).
     * @param string $template Nazwa widoku Blade, np. `mainboard::design-system.views.list`.
     * @param (Closure(): array<string, mixed>)|null $data Dane widoku, liczone dopiero przy jego otwarciu.
     *
     * @throws LogicException Gdy obszaru nie ma albo widok o tym kluczu już istnieje.
     */
    public function addView(string $area, string $key, string $labelKey, string $template, ?Closure $data = null): void;

    /**
     * Obszary z widokami w kolejności rejestracji.
     *
     * @return list<PreviewArea>
     */
    public function areas(): array;

    public function find(string $key): ?PreviewView;
}
