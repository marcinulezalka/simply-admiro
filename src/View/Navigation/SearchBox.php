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
 * Globalna wyszukiwarka na środku top baru aplikacji: formularz GET na adres strefy, pole `$name`. Opcjonalnie
 * zakresy do zaznaczenia (`$scopes`, pola `<$scopeName>[]`: po czym szukać) i podpowiedzi w trakcie pisania
 * (`$suggestUrl`: adres zwracający fragment HTML z widoku `admiro::search.suggestions`; js/global-search.js).
 */
final readonly class SearchBox
{
    /**
     * @param list<SearchScope> $scopes Zakresy wyszukiwania; pusta lista = bez wyboru.
     * @param string|null $suggestUrl Adres podpowiedzi (GET z tymi samymi polami co formularz); null = bez podpowiedzi.
     */
    public function __construct(
        public string $action,
        public string $placeholder,
        public string $name = 'q',
        public string $value = '',
        public array $scopes = [],
        public ?string $suggestUrl = null,
        public string $scopeName = 'in',
    ) {}
}
