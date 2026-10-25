{{-- Wygląd w ustawieniach konta (trasa account.appearance, Route::view): motyw (x-mainboard::theme-toggle: jasny, ciemny, kontrasty WCAG) i — gdy strefa ma kolorystyki do wyboru (`palettes`) — kolorystyka (x-mainboard::palette-switch); oba wybory zapamiętuje przeglądarka (js/theme.js), bez zapisu w koncie. $zone daje Core. --}}
<x-mainboard::zone.layout :title="text('account.appearance_title')" :meta="text('account.appearance_lead')">
    <div class="ss-grid">
        <section class="ss-card" aria-labelledby="appearance-theme-title">
            <h2 class="ss-card__title" id="appearance-theme-title">{{ text('account.theme_title') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('account.theme_hint') }}</p>
            <x-mainboard::theme-toggle />
        </section>

        @if ($zone->palettes !== [])
            <section class="ss-card" aria-labelledby="appearance-palette-title">
                <h2 class="ss-card__title" id="appearance-palette-title">{{ text('layout.palette') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('account.palette_hint') }}</p>
                <x-mainboard::palette-switch :palettes="$zone->palettes" />
            </section>
        @endif
    </div>
</x-mainboard::zone.layout>
