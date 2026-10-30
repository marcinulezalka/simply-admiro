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
 * (`$suggestUrl`: adres zwracający fragment HTML z widoku `admiro::search.suggestions`; js/global-search.js) oraz
 * akcje pod „+” przy polu w stylu okna czatu (`$actions`, np. dodaj organizację; x-admiro::app.search :compose)
 * z opcjonalną akcją główną na górze menu, oddzieloną separatorem (`$primaryAction`, np. nowa wizyta), i szybkie akcje
 * niezwiązane z dodawaniem pod błyskawicą obok „+” (`$findActions`, np. eksport).
 */
final readonly class SearchBox
{
    /**
     * @param list<SearchScope> $scopes Zakresy wyszukiwania; pusta lista = bez wyboru.
     * @param string|null $suggestUrl Adres podpowiedzi (GET z tymi samymi polami co formularz); null = bez podpowiedzi.
     * @param list<NavigationItem> $actions Akcje dodawania w menu „+”; pusta lista = bez menu.
     * @param list<NavigationItem>|null $findActions Szybkie akcje pod błyskawicą (np. eksport); null = bez błyskawicy,
     *        pusta lista = menu z komunikatem o braku akcji.
     * @param NavigationItem|null $primaryAction Akcja główna menu „+” — pierwsza, pod nią separator; null = bez niej.
     */
    public function __construct(
        public string $action,
        public string $placeholder,
        public string $name = 'q',
        public string $value = '',
        public array $scopes = [],
        public ?string $suggestUrl = null,
        public string $scopeName = 'in',
        public array $actions = [],
        public ?array $findActions = null,
        public ?NavigationItem $primaryAction = null,
    ) {}

    /**
     * Czy menu „+” ma jakąkolwiek akcję (główną albo pozostałe).
     */
    public function hasActions(): bool
    {
        return $this->primaryAction !== null || $this->actions !== [];
    }
}
