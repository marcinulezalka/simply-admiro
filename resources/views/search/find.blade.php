{{-- Menu szybkich akcji pod błyskawicą przy polu wyszukiwania (x-admiro::app.search :compose, obok „+”): akcje niezwiązane z dodawaniem (np. eksport) pod nagłówkiem „Szybkie akcje”; bez akcji — komunikat, że ich jeszcze nie ma. Ikona błyska przy najechaniu i po otwarciu (bez animacji przy prefers-reduced-motion). Panel to teczka Core (.ss-folders: zakładka „Szybkie akcje” z błyskawicą i skosem, tylna warstwa i arkusz z siatką kropek), a w niej akcje. $actions: list<View\Navigation\NavigationItem>. --}}
<details class="ss-search__menu ss-search__find" data-ss-dropdown>
    <summary class="ss-search__menu-toggle ss-search__find-toggle" title="{{ text('layout.search_find') }}">
        <x-admiro::icon name="zap" class="ss-search__menu-icon ss-search__find-icon" />
        <span class="ss-sr-only">{{ text('layout.search_find') }}</span>
    </summary>
    <div class="ss-search__menu-panel ss-search__find-panel">
        <div class="ss-folders ss-search__find-folders">
            <div class="ss-folders__tabs">
                <p class="ss-folder__tab ss-folder__tab--current"><x-admiro::icon name="zap" class="ss-folder__tab-icon" />{{ text('layout.search_find') }}</p>
            </div>
            <section class="ss-folder">
                <div class="ss-folder__back">
                    <div class="ss-folder__body">
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
                </div>
            </section>
        </div>
    </div>
</details>
