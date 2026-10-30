{{-- Narzędzia wyglądu na dole panelu bocznego (js/theme.js, bez skryptu ukryte): ustawienia (koło zębate, settingsUrl: ?string), tryby kolorów (jasny/ciemny, motywy wysokiego kontrastu WCAG) oraz rozmiar i powiększenie (A, AA, pomniejsz −, powiększ + — o jeden stopień rozmiaru tekstu); włączone (aria-pressed) w kolorach akcentu. grid (panel kalendarza w layoucie stref): jedna karta z tłem jak podgląd dnia (.ss-side-panel__tools--grid, bez kreski nad nią), w niej grupy jako osobne pigułki (.ss-side-panel__segment, jak przełącznik zakładek powiadomień): ustawienia, tryby kolorów (ciemny/jasny, kontrast WCAG, duży kontrast), rozmiar tekstu (A, AA) i powiększenie (−, +); podpisy grup tylko dla czytników ekranu. Bez grid — trzy podpisane wiersze. --}}
@props(['settingsUrl' => null, 'grid' => false])
@if ($grid)
    <div class="ss-side-panel__tools ss-side-panel__tools--grid" role="group" aria-label="{{ text('layout.appearance_tools') }}">
        @if ($settingsUrl !== null)
            <span class="ss-side-panel__segment" role="group" aria-label="{{ text('layout.settings') }}">
                <a class="ss-icon-button ss-side-panel__tool ss-side-panel__settings" href="{{ $settingsUrl }}" title="{{ text('layout.settings') }}"><x-admiro::icon name="settings-gear" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.settings') }}</span></a>
            </span>
        @endif
        <span class="ss-side-panel__segment" role="group" aria-label="{{ text('layout.color_modes') }}">
            <span class="ss-side-panel__tool-group" data-ss-theme-flip-control hidden>
                <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.theme_dark') }}" data-ss-theme-flip><x-admiro::icon name="moon" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.theme_dark') }}</span></button>
            </span>
            <span class="ss-side-panel__tool-group" data-ss-theme-toggle hidden>
                <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.themes.contrast') }}" data-ss-theme="contrast"><x-admiro::icon name="contrast" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.themes.contrast') }}</span></button>
                <button class="ss-icon-button ss-side-panel__tool" type="button" aria-pressed="false" title="{{ text('layout.themes.contrast-yellow') }}" data-ss-theme="contrast-yellow"><x-admiro::icon name="eye" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.themes.contrast-yellow') }}</span></button>
            </span>
        </span>
        <span class="ss-side-panel__segment" role="group" aria-label="{{ text('layout.preferences.text-size') }}" data-ss-preference="text-size" hidden>
            <button class="ss-icon-button ss-side-panel__tool ss-side-panel__tool--text" type="button" aria-pressed="false" title="{{ text('layout.text_sizes.normal') }}" data-ss-preference-value="normal">A<span class="ss-sr-only"> — {{ text('layout.text_sizes.normal') }}</span></button>
            <button class="ss-icon-button ss-side-panel__tool ss-side-panel__tool--text" type="button" aria-pressed="false" title="{{ text('layout.text_sizes.large') }}" data-ss-preference-value="large">AA<span class="ss-sr-only"> — {{ text('layout.text_sizes.large') }}</span></button>
        </span>
        <span class="ss-side-panel__segment" role="group" aria-label="{{ text('layout.zoom') }}" data-ss-preference="text-size" hidden>
            <button class="ss-icon-button ss-side-panel__tool" type="button" title="{{ text('layout.zoom_out') }}" data-ss-preference-step="-1"><x-admiro::icon name="zoom-out" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.zoom_out') }}</span></button>
            <button class="ss-icon-button ss-side-panel__tool" type="button" title="{{ text('layout.zoom_in') }}" data-ss-preference-step="1"><x-admiro::icon name="zoom-in" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.zoom_in') }}</span></button>
        </span>
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
