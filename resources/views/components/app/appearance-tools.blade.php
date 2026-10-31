{{-- Narzędzia wyglądu na dole panelu bocznego (js/theme.js, bez skryptu ukryte): ustawienia (koło zębate, settingsUrl: ?string), tryby kolorów (jasny/ciemny, motywy wysokiego kontrastu WCAG) oraz rozmiar i powiększenie (A, AA, pomniejsz −, powiększ + — o jeden stopień rozmiaru tekstu); włączone (aria-pressed) w kolorach akcentu. grid (panel kalendarza w layoucie stref): karta z tłem jak podgląd dnia (.ss-side-panel__tools--grid, bez kreski nad nią) z kafelkami po dwa w rzędzie, jak szybkie ustawienia systemu (.ss-quick-tile: ikona i podpis, bez tła, delikatna ramka): ustawienia (odnośnik), „Wiki” — przycisk otwierający prawy sidebar wiki strony (x-admiro::app.sidebar-wiki; widoczny tylko na stronie z sidebarem, js/sidebar-wiki.js), a grupy z kilkoma trybami — tryb kolorów (jasny, ciemny, kontrast WCAG, duży kontrast), rozmiar tekstu (A, AA) i powiększenie (pomniejsz, powiększ) — jako kafelki rozwijane (<details>, otwarty zajmuje cały rząd) z listą trybów, każdy z ikoną i nazwą, wybrany delikatnie zaznaczony (aria-pressed); wybór trybu zamyka listę (data-ss-tile-close, js/calendar-panel.js). Bez grid — trzy podpisane wiersze. --}}
@props(['settingsUrl' => null, 'grid' => false])
@if ($grid)
    <div class="ss-side-panel__tools ss-side-panel__tools--grid" role="group" aria-label="{{ text('layout.appearance_tools') }}">
        @if ($settingsUrl !== null)
            <a class="ss-quick-tile" href="{{ $settingsUrl }}"><x-admiro::icon name="settings-gear" class="ss-quick-tile__icon" /><span class="ss-quick-tile__label">{{ text('layout.settings') }}</span></a>
        @endif
        <button class="ss-quick-tile" type="button" title="{{ text('layout.sidebar_wiki_open') }}" aria-controls="ss-sidebar-wiki-panel" aria-expanded="false" data-ss-sidebar-wiki-open hidden><x-admiro::icon name="book-open" class="ss-quick-tile__icon" /><span class="ss-quick-tile__label">{{ text('layout.wiki') }}</span></button>
        <details class="ss-quick-tile ss-quick-tile--menu" data-ss-theme-toggle hidden>
            <summary class="ss-quick-tile__toggle"><x-admiro::icon name="contrast" class="ss-quick-tile__icon" /><span class="ss-quick-tile__label">{{ text('layout.color_modes') }}</span><x-admiro::icon name="chevron-down" class="ss-quick-tile__chevron" /></summary>
            <span class="ss-quick-tile__menu" role="group" aria-label="{{ text('layout.color_modes') }}">
                <button class="ss-quick-tile__option" type="button" aria-pressed="false" data-ss-theme="light" data-ss-tile-close><x-admiro::icon name="sun" class="ss-quick-tile__option-icon" /><span>{{ text('layout.themes.light') }}</span></button>
                <button class="ss-quick-tile__option" type="button" aria-pressed="false" data-ss-theme="dark" data-ss-tile-close><x-admiro::icon name="moon" class="ss-quick-tile__option-icon" /><span>{{ text('layout.themes.dark') }}</span></button>
                <button class="ss-quick-tile__option" type="button" aria-pressed="false" data-ss-theme="contrast" data-ss-tile-close><x-admiro::icon name="contrast" class="ss-quick-tile__option-icon" /><span>{{ text('layout.themes.contrast') }}</span></button>
                <button class="ss-quick-tile__option" type="button" aria-pressed="false" data-ss-theme="contrast-yellow" data-ss-tile-close><x-admiro::icon name="eye" class="ss-quick-tile__option-icon" /><span>{{ text('layout.themes.contrast-yellow') }}</span></button>
            </span>
        </details>
        <details class="ss-quick-tile ss-quick-tile--menu" data-ss-preference="text-size" hidden>
            <summary class="ss-quick-tile__toggle"><span class="ss-quick-tile__icon ss-quick-tile__glyph" aria-hidden="true">Aa</span><span class="ss-quick-tile__label">{{ text('layout.preferences.text-size') }}</span><x-admiro::icon name="chevron-down" class="ss-quick-tile__chevron" /></summary>
            <span class="ss-quick-tile__menu" role="group" aria-label="{{ text('layout.preferences.text-size') }}">
                <button class="ss-quick-tile__option" type="button" aria-pressed="false" data-ss-preference-value="normal" data-ss-tile-close><span class="ss-quick-tile__option-icon ss-quick-tile__glyph" aria-hidden="true">A</span><span>{{ text('layout.text_sizes.normal') }}</span></button>
                <button class="ss-quick-tile__option" type="button" aria-pressed="false" data-ss-preference-value="large" data-ss-tile-close><span class="ss-quick-tile__option-icon ss-quick-tile__glyph" aria-hidden="true">AA</span><span>{{ text('layout.text_sizes.large') }}</span></button>
            </span>
        </details>
        <details class="ss-quick-tile ss-quick-tile--menu" data-ss-preference="text-size" hidden>
            <summary class="ss-quick-tile__toggle"><x-admiro::icon name="zoom-in" class="ss-quick-tile__icon" /><span class="ss-quick-tile__label">{{ text('layout.zoom') }}</span><x-admiro::icon name="chevron-down" class="ss-quick-tile__chevron" /></summary>
            <span class="ss-quick-tile__menu" role="group" aria-label="{{ text('layout.zoom') }}">
                <button class="ss-quick-tile__option" type="button" data-ss-preference-step="-1"><x-admiro::icon name="zoom-out" class="ss-quick-tile__option-icon" /><span>{{ text('layout.zoom_out') }}</span></button>
                <button class="ss-quick-tile__option" type="button" data-ss-preference-step="1"><x-admiro::icon name="zoom-in" class="ss-quick-tile__option-icon" /><span>{{ text('layout.zoom_in') }}</span></button>
            </span>
        </details>
    </div>
@else
    <div class="ss-side-panel__tools" role="group" aria-label="{{ text('layout.appearance_tools') }}">
        @if ($settingsUrl !== null)
            <div class="ss-side-panel__row">
                <span class="ss-side-panel__row-label">{{ text('layout.settings') }}</span>
                <span class="ss-side-panel__tool-group">
                    <a class="ss-icon-button ss-side-panel__tool ss-side-panel__settings" href="{{ $settingsUrl }}" title="{{ text('layout.settings') }}"><x-admiro::icon name="settings-gear" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.settings') }}</span></a>
                </span>
            </div>
        @endif
        <div class="ss-side-panel__row">
            <span class="ss-side-panel__row-label">{{ text('layout.color_modes') }}</span>
            <span class="ss-side-panel__tool-group">
                <span class="ss-side-panel__tool-group" data-ss-theme-flip-control hidden>
                    <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.theme_dark') }}" data-ss-theme-flip><x-admiro::icon name="moon" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.theme_dark') }}</span></button>
                </span>
                <span class="ss-side-panel__tool-group" data-ss-theme-toggle hidden>
                    <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.themes.contrast') }}" data-ss-theme="contrast"><x-admiro::icon name="contrast" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.themes.contrast') }}</span></button>
                    <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.themes.contrast-yellow') }}" data-ss-theme="contrast-yellow"><x-admiro::icon name="eye" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.themes.contrast-yellow') }}</span></button>
                </span>
            </span>
        </div>
        <div class="ss-side-panel__row">
            <span class="ss-side-panel__row-label">{{ text('layout.resize_zoom') }}</span>
            <span class="ss-side-panel__tool-group" data-ss-preference="text-size" hidden>
                <button class="ss-icon-button ss-side-panel__tool ss-side-panel__tool--text" type="button" aria-pressed="false" title="{{ text('layout.text_sizes.normal') }}" data-ss-preference-value="normal">A<span class="ss-sr-only"> — {{ text('layout.text_sizes.normal') }}</span></button>
                <button class="ss-icon-button ss-side-panel__tool ss-side-panel__tool--text" type="button" aria-pressed="false" title="{{ text('layout.text_sizes.large') }}" data-ss-preference-value="large">AA<span class="ss-sr-only"> — {{ text('layout.text_sizes.large') }}</span></button>
                <button class="ss-icon-button ss-side-panel__tool" type="button" title="{{ text('layout.zoom_out') }}" data-ss-preference-step="-1"><x-admiro::icon name="zoom-out" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.zoom_out') }}</span></button>
                <button class="ss-icon-button ss-side-panel__tool" type="button" title="{{ text('layout.zoom_in') }}" data-ss-preference-step="1"><x-admiro::icon name="zoom-in" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.zoom_in') }}</span></button>
            </span>
        </div>
    </div>
@endif
