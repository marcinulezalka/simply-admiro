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
 * Jeden wynik globalnej wyszukiwarki: etykieta, adres, opcjonalnie opis (np. kod, nazwa własna), ikona albo flagi.
 */
final readonly class SearchResult
{
    /**
     * @param string|null $icon Nazwa ikony z `public/assets/img/icons/`; bez flag i ikony — kropka.
     * @param list<string> $flags Kody flag (x-admiro::flag), np. kraje języka; wygrywają z ikoną.
     */
    public function __construct(
        public string $label,
        public string $url,
        public ?string $description = null,
        public ?string $icon = null,
        public array $flags = [],
    ) {}
}
