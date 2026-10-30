{{-- Menu szybkich akcji pod błyskawicą przy polu wyszukiwania (x-admiro::app.search :compose, obok „+”): akcje niezwiązane z dodawaniem (np. eksport) pod nagłówkiem „Szybkie akcje”; bez akcji — komunikat, że ich jeszcze nie ma. Ikona błyska przy najechaniu i po otwarciu (bez animacji przy prefers-reduced-motion). $actions: list<View\Navigation\NavigationItem>. --}}
<details class="ss-search__menu ss-search__find" data-ss-dropdown>
    <summary class="ss-search__menu-toggle ss-search__find-toggle" title="{{ text('layout.search_find') }}">
        <x-admiro::icon name="zap" class="ss-search__menu-icon ss-search__find-icon" />
        <span class="ss-sr-only">{{ text('layout.search_find') }}</span>
    </summary>
    <div class="ss-search__menu-panel">
        <p class="ss-search__menu-title">{{ text('layout.search_find') }}</p>
        @if ($actions === [])
            <p class="ss-search__menu-empty">{{ text('layout.search_find_empty') }}</p>
        @else
            <ul class="ss-account-menu__list">
                @foreach ($actions as $action)
                    <li><a class="ss-account-menu__link" href="{{ $action->url }}"><x-admiro::icon :name="$action->icon" class="ss-account-menu__icon" />{{ $action->label }}</a></li>
                @endforeach
            </ul>
        @endif
    </div>
</details>
