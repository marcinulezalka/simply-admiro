{{-- Flaga kraju lub języka z kodu `flag_icon` (flag-icons 4:3 z outhebox/blade-flags, inline SVG). Dekoracyjna: nazwę kraju lub języka podaje tekst obok. Bez kodu nic nie renderuje. --}}
@props(['icon' => null])
@if (is_string($icon) && $icon !== '')
    {{ svg('flag-flat-country-' . $icon, 'ss-flag', ['aria-hidden' => 'true', 'focusable' => 'false']) }}
@endif
