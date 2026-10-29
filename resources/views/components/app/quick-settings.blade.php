{{-- Szybkie ustawienia w ramce narzędzi stopki Workspace (jak w Windows czy Ubuntu): przycisk z ikoną suwaków `settings` otwiera nad stopką, od prawej, panel kafelków (<details data-ss-dropdown>) — skróty strefy (items: list<View\Navigation\NavigationItem> z Contracts\ZoneShortcuts, np. ustawienia globalne), dashboard (homeUrl, pomijany, gdy skrót ma ten sam adres), mapa aplikacji (#ss-sitemap) i tryb ciemny (przycisk [data-ss-theme-flip] w js/theme.js przełącza jasny ↔ ciemny, aria-pressed przy ciemnym; bez skryptu ukryty). Kafelek: ikona nad krótką etykietą, bieżąca strona i włączony tryb w kolorach akcentu. --}}
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
        <p class="ss-quick-settings__title">{{ text('layout.quick_settings') }}</p>
        <ul class="ss-quick-settings__tiles">
            @foreach ($tiles as $tile)
                <li>
                    <a class="ss-quick-settings__tile" href="{{ $tile->url }}" @if ($tile->current) aria-current="page" @endif>
                        <x-admiro::icon :name="$tile->icon" class="ss-quick-settings__icon" />
                        <span class="ss-quick-settings__label">{{ $tile->label }}</span>
                    </a>
                </li>
            @endforeach
            <li data-ss-theme-flip-control hidden>
                <button class="ss-quick-settings__tile" type="button" aria-pressed="false" data-ss-theme-flip>
                    <x-admiro::icon name="moon" class="ss-quick-settings__icon" />
                    <span class="ss-quick-settings__label">{{ text('layout.theme_dark') }}</span>
                </button>
            </li>
        </ul>
    </div>
</details>
