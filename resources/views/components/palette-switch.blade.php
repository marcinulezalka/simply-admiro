{{-- Przełącznik kolorystyki (js/theme.js) nakładanej na motyw: menu z próbkami — kolory bazowe strefy i kolorystyki z listy (Enums\Palette, tokeny --ss-palette-*); wybrana oznaczona haczykiem i aria-pressed, zapamiętana w przeglądarce osobno dla zakresu (scope, np. strefy). Ukryty bez skryptu i przy pustej liście. Strona musi przekazać te same palettes i scope do x-mainboard::page (meta dla skryptu w <head>). palettes: list<Enums\Palette>; panel: dodatkowa klasa panelu (np. ss-sidebar__panel). --}}
@props(['palettes' => [], 'panel' => null])
@if ($palettes !== [])
    <details {{ $attributes->merge(['class' => 'ss-account-menu ss-palette-switch']) }} data-ss-dropdown data-ss-palette-switch hidden>
        <summary class="ss-icon-button ss-palette-switch__toggle" title="{{ text('layout.palette') }}">
            <span class="ss-palette-swatch ss-palette-switch__current" aria-hidden="true"></span>
            <span class="ss-sr-only">{{ text('layout.palette') }}</span>
        </summary>
        <div @class(['ss-account-menu__panel', $panel])>
            <p class="ss-context-switch__group">{{ text('layout.palette') }}</p>
            <ul class="ss-account-menu__list">
                <li>
                    <button class="ss-account-menu__link" type="button" aria-pressed="false" data-ss-palette="">
                        <span class="ss-palette-swatch ss-palette-swatch--default" aria-hidden="true"></span>
                        <span>{{ text('layout.palettes.default') }}</span>
                        <x-mainboard::icon name="check" class="ss-context-switch__check ss-palette-switch__check" />
                    </button>
                </li>
                @foreach ($palettes as $palette)
                    <li>
                        <button class="ss-account-menu__link" type="button" aria-pressed="false" data-ss-palette="{{ $palette->value }}">
                            <span class="ss-palette-swatch ss-palette-swatch--{{ $palette->value }}" aria-hidden="true"></span>
                            <span>{{ $palette->label() }}</span>
                            <x-mainboard::icon name="check" class="ss-context-switch__check ss-palette-switch__check" />
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </details>
@endif
