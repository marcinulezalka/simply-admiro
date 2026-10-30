{{-- Menu szybkich akcji pod błyskawicą przy polu wyszukiwania (x-admiro::app.search :compose, obok „+”): akcje niezwiązane z dodawaniem (np. eksport) pod nagłówkiem „Szybkie akcje”; bez akcji — komunikat, że ich jeszcze nie ma. Ikona błyska przy najechaniu i po otwarciu (bez animacji przy prefers-reduced-motion). W tle panelu dekoracja (.ss-search__find-art, aria-hidden): dwie zaokrąglone ukośne kreski i duża, lekko wypukła błyskawica w tonie tła z miękkim cieniem; w layoucie stref także ukośne pasy i ziarno tła. $actions: list<View\Navigation\NavigationItem>. --}}
<details class="ss-search__menu ss-search__find" data-ss-dropdown>
    <summary class="ss-search__menu-toggle ss-search__find-toggle" title="{{ text('layout.search_find') }}">
        <x-admiro::icon name="zap" class="ss-search__menu-icon ss-search__find-icon" />
        <span class="ss-sr-only">{{ text('layout.search_find') }}</span>
    </summary>
    <div class="ss-search__menu-panel">
        <svg class="ss-search__find-art" viewBox="0 0 160 160" aria-hidden="true" focusable="false">
            <line class="ss-search__find-stroke" x1="46" y1="150" x2="150" y2="46" />
            <line class="ss-search__find-stroke" x1="78" y1="158" x2="158" y2="78" />
            <path class="ss-search__find-bolt" d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z" transform="translate(8 8) scale(6)" />
        </svg>
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
