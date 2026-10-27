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
 * Jeden wynik globalnej wyszukiwarki: etykieta, adres, opcjonalnie opis (np. kod, nazwa własna), ikona albo flagi,
 * a w podpowiedziach przełącznik stanu obok wyniku (np. włączenie organizacji) albo plakietka (np. „Domyślna”).
 */
final readonly class SearchResult
{
    /**
     * @param string|null $icon Nazwa ikony z `public/assets/img/icons/`; bez flag i ikony — kropka.
     * @param list<string> $flags Kody flag (x-admiro::flag), np. kraje języka; wygrywają z ikoną.
     * @param string|null $toggleUrl Adres POST przełącznika stanu w podpowiedzi (js/post-toggle.js), np. włączenie organizacji.
     * @param bool $active Stan przełącznika.
     * @param string|null $badge Plakietka zamiast przełącznika (element domyślny i wymagany, np. „Domyślna”).
     * @param string|null $badgeIcon Ikona w plakietce (np. `ticket-slash` przy wpisie domyślnym).
     */
    public function __construct(
        public string $label,
        public string $url,
        public ?string $description = null,
        public ?string $icon = null,
        public array $flags = [],
        public ?string $toggleUrl = null,
        public bool $active = false,
        public ?string $badge = null,
        public ?string $badgeIcon = null,
    ) {}
}
