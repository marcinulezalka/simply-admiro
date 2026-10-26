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
 * Grupa wyników globalnej wyszukiwarki (np. „Języki”) z adresem pełnej listy, gdy wyników jest więcej.
 */
final readonly class SearchResultGroup
{
    /**
     * @param list<SearchResult> $results
     * @param string|null $moreUrl Adres wszystkich wyników grupy; null = wszystkie są na liście.
     */
    public function __construct(
        public string $label,
        public array $results,
        public ?string $moreUrl = null,
    ) {}
}
