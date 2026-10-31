{{-- Globalna wyszukiwarka layoutu aplikacji (View\Navigation\SearchBox): formularz GET z polem (skrót „/”), opcjonalnie menu zakresów „Szukaj w” (przełączniki z ikonami, pola `<scopeName>[]`) i lista podpowiedzi (suggestUrl, js/global-search.js). compose: pole w stylu okna czatu (wariant Workspace, środek stopki treści): szare tło, po lewej „+” z menu szybkich akcji (search->actions, np. dodaj organizację; admiro::search.actions) zamiast lupy i suwaki zakresów, po prawej zaokrąglony przycisk szukania ze strzałką Entera zamiast skrótu „/”; podpowiedzi i menu otwierają się nad polem; za „+” błyskawica z szybkimi akcjami niezwiązanymi z dodawaniem (search->findActions, admiro::search.find; null = bez niej). folders: menu „+” jak teczka, jak menu błyskawicy (layout stref). commandUrl: adres formularza komend (POST, #ss-command-form za formularzem wyszukiwarki) — pole komend „/” na dole menu błyskawicy (admiro::search.commands; layout stref), null = bez niego. trailingScopes: suwaki zakresów między polem a przyciskiem szukania (layout stref) zamiast po lewej. Bez compose — lupa, pole, skrót „/” i suwaki po prawej (top bar). search: SearchBox. --}}
@props(['search', 'compose' => false, 'trailingScopes' => false, 'folders' => false, 'commandUrl' => null])
<form @class(['ss-search', 'ss-search--compose' => $compose]) role="search" method="GET" action="{{ $search->action }}"@if ($search->suggestUrl !== null) data-ss-search-suggest="{{ $search->suggestUrl }}"@endif>
    <label class="ss-sr-only" for="ss-global-search">{{ text('layout.search') }}</label>
    @if ($compose && $search->hasActions())
        @include('admiro::search.actions', ['actions' => $search->actions, 'primary' => $search->primaryAction, 'folder' => $folders])
    @endif
    @if ($compose && $search->findActions !== null)
        @include('admiro::search.find', ['actions' => $search->findActions, 'commandUrl' => $commandUrl])
    @endif
    @if ($compose && ! $trailingScopes && $search->scopes !== [])
        @include('admiro::search.scopes', ['search' => $search])
    @elseif (! $compose || (! $search->hasActions() && $search->findActions === null))
        <x-admiro::icon name="search" class="ss-search__icon" />
    @endif
    <input class="ss-search__input" id="ss-global-search" name="{{ $search->name }}" type="search" value="{{ $search->value }}" placeholder="{{ $search->placeholder }}" autocomplete="off" aria-describedby="ss-global-search-hint" @if ($search->suggestUrl !== null) role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ss-search-suggestions" @endif data-ss-search>
    @if ($compose && $trailingScopes && $search->scopes !== [])
        @include('admiro::search.scopes', ['search' => $search])
    @endif
    @if ($compose)
        <span class="ss-sr-only" id="ss-global-search-hint">{{ text('layout.search_shortcut') }}</span>
        <button class="ss-search__send" type="submit">
            <x-admiro::icon name="corner-down-left" class="ss-search__send-icon" />
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
@if ($compose && $search->findActions !== null && $commandUrl !== null)
    <form class="ss-command__form" id="ss-command-form" method="POST" action="{{ $commandUrl }}" hidden data-ss-command-form>
        @csrf
    </form>
@endif
