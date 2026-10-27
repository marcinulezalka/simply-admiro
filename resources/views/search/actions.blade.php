{{-- Menu akcji „+” przy polu wyszukiwania (x-admiro::app.search :compose, krok „Organizacje” kreatora onboardingu): przycisk z plusem otwiera listę akcji dodawania pod nagłówkiem „Dodaj” oddzielonym linią od pozycji. $actions: list<View\Navigation\NavigationItem>. --}}
<details class="ss-search__menu ss-search__actions" data-ss-dropdown>
    <summary class="ss-search__menu-toggle" title="{{ text('layout.search_actions') }}">
        <x-admiro::icon name="plus" class="ss-search__menu-icon" />
        <span class="ss-sr-only">{{ text('layout.search_actions') }}</span>
    </summary>
    <div class="ss-search__menu-panel">
        <p class="ss-search__menu-title">{{ text('layout.search_actions') }}</p>
        <ul class="ss-account-menu__list">
            @foreach ($actions as $action)
                <li><a class="ss-account-menu__link" href="{{ $action->url }}"><x-admiro::icon :name="$action->icon" class="ss-account-menu__icon" />{{ $action->label }}</a></li>
            @endforeach
        </ul>
    </div>
</details>
