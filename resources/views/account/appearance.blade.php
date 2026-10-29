{{-- Wygląd w ustawieniach konta (trasa account.appearance, Route::view): motyw (x-admiro::theme-toggle: jasny, ciemny, kontrasty WCAG), rozmiar tekstu i szerokość treści (x-admiro::preference-toggle, View\AppearanceChoices), a gdy strefa je ma — kolorystyka (`palettes`, x-admiro::palette-switch) i krój pisma (`font_styles`); wszystkie wybory zapamiętuje przeglądarka (js/theme.js), bez zapisu w koncie. $zone daje Core. --}}
@php($choices = app(\Simplysmart\Admiro\View\AppearanceChoices::class))
@php($fontStyles = $choices->fontStyles($zone))
<x-admiro::zone.layout :title="text('account.appearance_title')">
    <div class="ss-grid">
        <section class="ss-card" aria-labelledby="appearance-theme-title">
            <h2 class="ss-card__title" id="appearance-theme-title">{{ text('account.theme_title') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('account.theme_hint') }}</p>
            <x-admiro::theme-toggle />
        </section>

        <section class="ss-card" aria-labelledby="appearance-text-size-title">
            <h2 class="ss-card__title" id="appearance-text-size-title">{{ text('account.text_size_title') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('account.text_size_hint') }}</p>
            <x-admiro::preference-toggle :preference="\Simplysmart\Admiro\Enums\AppearancePreference::TextSize" :options="$choices->textSizes()" />
        </section>

        <section class="ss-card" aria-labelledby="appearance-layout-width-title">
            <h2 class="ss-card__title" id="appearance-layout-width-title">{{ text('account.layout_width_title') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('account.layout_width_hint') }}</p>
            <x-admiro::preference-toggle :preference="\Simplysmart\Admiro\Enums\AppearancePreference::LayoutWidth" :options="$choices->layoutWidths()" labels />
        </section>

        @if ($fontStyles !== [])
            <section class="ss-card" aria-labelledby="appearance-font-title">
                <h2 class="ss-card__title" id="appearance-font-title">{{ text('account.font_title') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('account.font_hint') }}</p>
                <x-admiro::preference-toggle :preference="\Simplysmart\Admiro\Enums\AppearancePreference::FontStyle" :options="$fontStyles" labels />
            </section>
        @endif

        @if ($zone->palettes !== [])
            <section class="ss-card" aria-labelledby="appearance-palette-title">
                <h2 class="ss-card__title" id="appearance-palette-title">{{ text('layout.palette') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('account.palette_hint') }}</p>
                <x-admiro::palette-switch :palettes="$zone->palettes" />
            </section>
        @endif
    </div>
</x-admiro::zone.layout>
