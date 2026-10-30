{{-- Menu szybkich akcji pod błyskawicą przy polu wyszukiwania (x-admiro::app.search :compose, obok „+”): akcje niezwiązane z dodawaniem (np. eksport) pod nagłówkiem „Szybkie akcje”; bez akcji — komunikat, że ich jeszcze nie ma. Ikona błyska przy najechaniu i po otwarciu (bez animacji przy prefers-reduced-motion). Panel jak teczka: tło (kolor tylnej warstwy teczek) schodzi wypustką w dół i obejmuje przycisk błyskawicy, na nim podpis „Szybkie akcje” i akcje krojem mono, pod nimi arkusz z siatką kropek (bez akcji — komunikat w arkuszu). $actions: list<View\Navigation\NavigationItem>. --}}
<details class="ss-search__menu ss-search__find" data-ss-dropdown>
    <summary class="ss-search__menu-toggle ss-search__find-toggle" title="{{ text('layout.search_find') }}">
        <x-admiro::icon name="zap" class="ss-search__menu-icon ss-search__find-icon" />
        <span class="ss-sr-only">{{ text('layout.search_find') }}</span>
    </summary>
    <div class="ss-search__menu-panel ss-search__find-panel">
        <div class="ss-search__find-body">
            <p class="ss-search__find-title">{{ text('layout.search_find') }}</p>
            @if ($actions !== [])
                <ul class="ss-search__find-actions">
                    @foreach ($actions as $action)
                        <li><a class="ss-search__find-action" href="{{ $action->url }}"><x-admiro::icon :name="$action->icon" class="ss-search__find-action-icon" />{{ $action->label }}</a></li>
                    @endforeach
                </ul>
            @endif
            <div class="ss-search__find-sheet">
                @if ($actions === [])
                    <p class="ss-search__menu-empty">{{ text('layout.search_find_empty') }}</p>
                @endif
            </div>
        </div>
        <span class="ss-search__find-tab" aria-hidden="true"></span>
    </div>
</details>
