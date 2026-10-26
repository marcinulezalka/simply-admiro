{{-- Globalna wyszukiwarka layoutu aplikacji (View\Navigation\SearchBox): formularz GET z polem (skrót „/”), opcjonalnie menu zakresów „Szukaj w” (przełączniki z ikonami, pola `<scopeName>[]`) i lista podpowiedzi (suggestUrl, js/global-search.js). compose: pole w stylu okna czatu (wariant Workspace, środek stopki treści): szare tło, po lewej „+” z menu akcji (search->actions, np. dodaj organizację) i suwaki zakresów zamiast lupy, po prawej zaokrąglony przycisk szukania z rakietą zamiast skrótu „/”; podpowiedzi i menu otwierają się nad polem. Bez compose — lupa, pole, skrót „/” i suwaki po prawej (top bar). search: SearchBox. --}}
@props(['search', 'compose' => false])
<form @class(['ss-search', 'ss-search--compose' => $compose]) role="search" method="GET" action="{{ $search->action }}"@if ($search->suggestUrl !== null) data-ss-search-suggest="{{ $search->suggestUrl }}"@endif>
    <label class="ss-sr-only" for="ss-global-search">{{ text('layout.search') }}</label>
    @if ($compose && $search->actions !== [])
        <details class="ss-search__menu ss-search__actions" data-ss-dropdown>
            <summary class="ss-search__menu-toggle" title="{{ text('layout.search_actions') }}">
                <x-admiro::icon name="plus" class="ss-search__menu-icon" />
                <span class="ss-sr-only">{{ text('layout.search_actions') }}</span>
            </summary>
            <div class="ss-search__menu-panel">
                <p class="ss-search__scopes-title">{{ text('layout.search_actions') }}</p>
                <ul class="ss-account-menu__list">
                    @foreach ($search->actions as $action)
                        <li><a class="ss-account-menu__link" href="{{ $action->url }}"><x-admiro::icon :name="$action->icon" class="ss-account-menu__icon" />{{ $action->label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </details>
    @endif
    @if ($compose && $search->scopes !== [])
        @include('admiro::search.scopes', ['search' => $search])
    @else
        <x-admiro::icon name="search" class="ss-search__icon" />
    @endif
    <input class="ss-search__input" id="ss-global-search" name="{{ $search->name }}" type="search" value="{{ $search->value }}" placeholder="{{ $search->placeholder }}" autocomplete="off" aria-describedby="ss-global-search-hint" @if ($search->suggestUrl !== null) role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ss-search-suggestions" @endif data-ss-search>
    @if ($compose)
        <span class="ss-sr-only" id="ss-global-search-hint">{{ text('layout.search_shortcut') }}</span>
        <button class="ss-search__send" type="submit">
            <x-admiro::icon name="rocket" class="ss-search__send-icon" />
            <span class="ss-sr-only">{{ text('layout.search_submit') }}</span>
        </button>
    @else
        <kbd class="ss-search__kbd" id="ss-global-search-hint" title="{{ text('layout.search_shortcut') }}"><span aria-hidden="true">/</span><span class="ss-sr-only">{{ text('layout.search_shortcut') }}</span></kbd>
        @if ($search->scopes !== [])
            @include('admiro::search.scopes', ['search' => $search])
        @endif
    @endif
    @if ($search->suggestUrl !== null)
        <div class="ss-search__suggestions" id="ss-search-suggestions" role="listbox" aria-label="{{ text('layout.search_suggestions') }}" hidden data-ss-search-suggestions></div>
    @endif
</form>
