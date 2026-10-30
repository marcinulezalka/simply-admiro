{{-- Wygląd w ustawieniach konta „Personalizacja” (trasa account.appearance, Route::view) w kolejności: szerokość treści (układ), motyw (x-admiro::theme-toggle: jasny, ciemny, kontrasty WCAG), kolorystyka (gdy strefa ma `palettes`, x-admiro::palette-switch), krój pisma (gdy strefa ma `font_styles`) i rozmiar tekstu (x-admiro::preference-toggle, View\AppearanceChoices). Pod tytułem, gdzie trafiają wybory: w strefie z `appearance.account` od razu do konta (js/theme.js → account.appearance.update; domyślny wygląd aplikacji nadpisany wyborami konta, Profile\AccountAppearance) z przyciskiem przywrócenia domyślnego wyglądu (DELETE account.appearance.reset), inaczej do przeglądarki. $zone daje Core. --}}
@php($choices = app(\Simplysmart\Admiro\View\AppearanceChoices::class))
@php($fontStyles = $choices->fontStyles($zone))
@php($inAccount = $zone->appearance->account)
<x-admiro::zone.layout :title="text('account.appearance_title')" :meta="text($inAccount ? 'account.appearance_storage_account' : 'account.appearance_storage_browser')">
    <div class="ss-grid">
        <section class="ss-card" aria-labelledby="appearance-layout-width-title">
            <h2 class="ss-card__title" id="appearance-layout-width-title">{{ text('account.layout_width_title') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('account.layout_width_hint') }}</p>
            <x-admiro::preference-toggle :preference="\Simplysmart\Admiro\Enums\AppearancePreference::LayoutWidth" :options="$choices->layoutWidths()" labels />
        </section>

        <section class="ss-card" aria-labelledby="appearance-theme-title">
            <h2 class="ss-card__title" id="appearance-theme-title">{{ text('account.theme_title') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('account.theme_hint') }}</p>
            <x-admiro::theme-toggle />
        </section>

        @if ($zone->palettes !== [])
            <section class="ss-card" aria-labelledby="appearance-palette-title">
                <h2 class="ss-card__title" id="appearance-palette-title">{{ text('layout.palette') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('account.palette_hint') }}</p>
                <x-admiro::palette-switch :palettes="$zone->palettes" />
            </section>
        @endif

        @if ($fontStyles !== [])
            <section class="ss-card" aria-labelledby="appearance-font-title">
                <h2 class="ss-card__title" id="appearance-font-title">{{ text('account.font_title') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('account.font_hint') }}</p>
                <x-admiro::preference-toggle :preference="\Simplysmart\Admiro\Enums\AppearancePreference::FontStyle" :options="$fontStyles" labels />
            </section>
        @endif

        <section class="ss-card" aria-labelledby="appearance-text-size-title">
            <h2 class="ss-card__title" id="appearance-text-size-title">{{ text('account.text_size_title') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('account.text_size_hint') }}</p>
            <x-admiro::preference-toggle :preference="\Simplysmart\Admiro\Enums\AppearancePreference::TextSize" :options="$choices->textSizes()" />
        </section>

        @if ($inAccount)
            <form method="POST" action="{{ route($zone->routeName(\Simplysmart\Admiro\Http\AccountRoutes::APPEARANCE_RESET)) }}">
                @csrf
                @method('DELETE')
                <button class="ss-button ss-button--pill" type="submit"><x-admiro::icon name="refresh" class="ss-button__icon" />{{ text('account.appearance_reset') }}</button>
            </form>
        @endif
    </div>
</x-admiro::zone.layout>
