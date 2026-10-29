{{-- Szybkie ustawienia w ramce narzędzi stopki Workspace: przycisk z ikoną suwaków `settings` otwiera nad stopką, od prawej, menu w stylu menu konta po lewej (details z data-ss-dropdown, lista .ss-account-menu__list z ikonami) — skróty strefy (items: list<View\Navigation\NavigationItem> z Contracts\ZoneShortcuts, np. ustawienia globalne), dashboard (homeUrl, pomijany, gdy skrót ma ten sam adres) i mapa aplikacji (#ss-sitemap); pod linią tryb ciemny (przycisk [data-ss-theme-flip] w js/theme.js przełącza jasny ↔ ciemny, haczyk przy włączonym — aria-pressed; bez skryptu ukryty). Bieżąca strona wyróżniona jak w menu konta. --}}
@props(['items' => [], 'homeUrl' => null, 'toggleClass' => 'ss-icon-button'])
@php
    $tiles = $items;

    if ($homeUrl !== null && ! in_array($homeUrl, array_map(static fn (\Simplysmart\Admiro\View\Navigation\NavigationItem $item): string => $item->url, $items), true)) {
        $tiles[] = new \Simplysmart\Admiro\View\Navigation\NavigationItem(text('layout.dashboard'), $homeUrl, 'home', request()->url() === $homeUrl);
    }

    $tiles[] = new \Simplysmart\Admiro\View\Navigation\NavigationItem(text('layout.sitemap_open'), '#' . \Simplysmart\Admiro\View\Navigation\NavigationItem::SITEMAP_ANCHOR, 'sitemap');
@endphp
<details class="ss-account-menu ss-quick-settings" data-ss-dropdown>
    <summary class="{{ $toggleClass }} ss-quick-settings__toggle" title="{{ text('layout.quick_settings') }}">
        <x-admiro::icon name="settings" class="ss-icon-button__icon" />
        <span class="ss-sr-only">{{ text('layout.quick_settings') }}</span>
    </summary>
    <div class="ss-account-menu__panel ss-quick-settings__panel">
        <ul class="ss-account-menu__list" aria-label="{{ text('layout.quick_settings') }}">
            @foreach ($tiles as $tile)
                <li><a class="ss-account-menu__link" href="{{ $tile->url }}" @if ($tile->current) aria-current="page" @endif><x-admiro::icon :name="$tile->icon" class="ss-account-menu__icon" />{{ $tile->label }}</a></li>
            @endforeach
        </ul>
        <div class="ss-quick-settings__theme" data-ss-theme-flip-control hidden>
            <button class="ss-account-menu__link ss-quick-settings__flip" type="button" aria-pressed="false" data-ss-theme-flip>
                <x-admiro::icon name="moon" class="ss-account-menu__icon" />{{ text('layout.theme_dark') }}<x-admiro::icon name="check" class="ss-quick-settings__check" />
            </button>
        </div>
    </div>
</details>
