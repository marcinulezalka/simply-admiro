{{-- Przełącznik motywu jasny / ciemny (js/theme.js): przycisk z aria-pressed = motyw ciemny włączony; ikona pokazuje motyw, na który przełączy (księżyc w jasnym, słońce w ciemnym). Ukryty bez skryptu (działa wtedy motyw systemu). --}}
<button {{ $attributes->merge(['class' => 'ss-icon-button ss-theme-toggle']) }} type="button" aria-pressed="false" title="{{ text('layout.dark_theme') }}" data-ss-theme-toggle hidden>
    <x-mainboard::icon name="moon" class="ss-icon-button__icon ss-theme-toggle__moon" />
    <x-mainboard::icon name="sun" class="ss-icon-button__icon ss-theme-toggle__sun" />
    <span class="ss-sr-only">{{ text('layout.dark_theme') }}</span>
</button>
