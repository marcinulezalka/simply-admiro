{{-- Panel menu wyszukiwarki jak teczka (menu błyskawicy admiro::search.find i „+” admiro::search.actions w layoucie stref; details z klasą .ss-search__menu--folder): u góry zakładka z ikoną i tytułem (falisty skos jak zakładki teczek Core), pod nią tło w kolorze tylnej warstwy teczek z odnośnikami w zwykłym stylu menu — ikona i tekst — (akcja główna na górze, pod nią separator), pod nimi arkusz z siatką kropek na całą szerokość teczki (bez akcji — komunikat empty, gdy jest), z $commandUrl pod arkuszem dolna sekcja w kolorze tła teczki z polem komend systemu admiro::search.commands, a tło schodzi w dół wypustką z tą samą falą i obejmuje przycisk menu. $icon: string; $title: string; $actions: list<View\Navigation\NavigationItem>; $primary: ?View\Navigation\NavigationItem; $empty: ?string; $commandUrl: ?string. --}}
@php($primary ??= null)
@php($empty ??= null)
@php($commandUrl ??= null)
<div class="ss-search__menu-panel ss-search__folder">
    <p class="ss-search__folder-head"><x-admiro::icon :name="$icon" class="ss-search__folder-head-icon" />{{ $title }}</p>
    <div class="ss-search__folder-body">
        @if ($primary !== null)
            <ul class="ss-account-menu__list">
                <li><a class="ss-account-menu__link" href="{{ $primary->url }}"><x-admiro::icon :name="$primary->icon" class="ss-account-menu__icon" />{{ $primary->label }}</a></li>
            </ul>
            @if ($actions !== [])
                <hr class="ss-search__menu-separator">
            @endif
        @endif
        @if ($actions !== [])
            <ul class="ss-account-menu__list">
                @foreach ($actions as $action)
                    <li><a class="ss-account-menu__link" href="{{ $action->url }}"><x-admiro::icon :name="$action->icon" class="ss-account-menu__icon" />{{ $action->label }}</a></li>
                @endforeach
            </ul>
        @endif
        <div class="ss-search__folder-sheet">
            @if ($empty !== null && $actions === [] && $primary === null)
                <p class="ss-search__menu-empty">{{ $empty }}</p>
            @endif
        </div>
        @if ($commandUrl !== null)
            <div class="ss-search__folder-foot">
                @include('admiro::search.commands')
            </div>
        @endif
    </div>
    <span class="ss-search__folder-tab" aria-hidden="true"></span>
</div>
