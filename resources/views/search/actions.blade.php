{{-- Menu akcji „+” przy polu wyszukiwania (x-admiro::app.search :compose, krok „Organizacje” kreatora onboardingu): przycisk z plusem otwiera listę akcji dodawania pod nagłówkiem „Dodaj” oddzielonym linią od pozycji; akcja główna (np. nowa wizyta) na górze, pod nią separator; z $folder (layout stref) panel jak teczka (admiro::search.folder, jak menu błyskawicy) z zakładką „Dodaj”. $actions: list<View\Navigation\NavigationItem>; $primary: ?View\Navigation\NavigationItem (domyślnie null); $folder: bool (domyślnie false). --}}
@php($primary ??= null)
@php($folder ??= false)
<details @class(['ss-search__menu', 'ss-search__menu--folder' => $folder, 'ss-search__actions']) data-ss-dropdown>
    <summary class="ss-search__menu-toggle" title="{{ text('layout.search_actions') }}">
        <x-admiro::icon name="plus" class="ss-search__menu-icon" />
        <span class="ss-sr-only">{{ text('layout.search_actions') }}</span>
    </summary>
    @if ($folder)
        @include('admiro::search.folder', ['icon' => 'plus', 'title' => text('layout.search_actions'), 'actions' => $actions, 'primary' => $primary])
    @else
        <div class="ss-search__menu-panel">
            <p class="ss-search__menu-title">{{ text('layout.search_actions') }}</p>
            @if ($primary !== null)
                <ul class="ss-account-menu__list">
                    <li><a class="ss-account-menu__link" href="{{ $primary->url }}"><x-admiro::icon :name="$primary->icon" class="ss-account-menu__icon" />{{ $primary->label }}</a></li>
                </ul>
                @if ($actions !== [])
                    <hr class="ss-search__menu-separator">
                @endif
            @endif
            <ul class="ss-account-menu__list">
                @foreach ($actions as $action)
                    <li><a class="ss-account-menu__link" href="{{ $action->url }}"><x-admiro::icon :name="$action->icon" class="ss-account-menu__icon" />{{ $action->label }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif
</details>
