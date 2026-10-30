{{-- Menu szybkich akcji pod błyskawicą przy polu wyszukiwania (x-admiro::app.search :compose, obok „+”): akcje niezwiązane z dodawaniem (np. eksport) w panelu jak teczka (admiro::search.folder) z zakładką „Szybkie akcje”; bez akcji — komunikat, że ich jeszcze nie ma; z $commandUrl na dole pole komend systemu (admiro::search.commands, „/” z `todo:`). Ikona błyska przy najechaniu i po otwarciu (bez animacji przy prefers-reduced-motion). $actions: list<View\Navigation\NavigationItem>; $commandUrl: ?string (domyślnie null). --}}
@php($commandUrl ??= null)
<details class="ss-search__menu ss-search__menu--folder ss-search__find" data-ss-dropdown>
    <summary class="ss-search__menu-toggle ss-search__find-toggle" title="{{ text('layout.search_find') }}">
        <x-admiro::icon name="zap" class="ss-search__menu-icon ss-search__find-icon" />
        <span class="ss-sr-only">{{ text('layout.search_find') }}</span>
    </summary>
    @include('admiro::search.folder', ['icon' => 'zap', 'title' => text('layout.search_find'), 'actions' => $actions, 'empty' => text('layout.search_find_empty'), 'commandUrl' => $commandUrl])
</details>
