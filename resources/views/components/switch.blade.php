{{-- Przełącznik włącz/wyłącz (.ss-switch): checkbox z role="switch", opis i podpowiedź po lewej, tor po prawej. id: identyfikator pola; label, hint: teksty; checked: stan; hideLabel: etykieta tylko dla czytników ekranu (np. przełącznik w wierszu tabeli); icon: ikona przed etykietą; class: dodatkowa klasa etykiety; reszta atrybutów trafia na input (np. name, disabled). --}}
@props(['id', 'label', 'hint' => null, 'checked' => false, 'hideLabel' => false, 'icon' => null, 'class' => null])
<label @class(['ss-switch', (string) $class => filled($class)]) for="{{ $id }}">
    <span class="ss-switch__text">
        <span @class(['ss-sr-only' => $hideLabel, 'ss-switch__label' => $icon !== null])>@if ($icon !== null)<x-admiro::icon :name="$icon" class="ss-switch__icon" />@endif{{ $label }}</span>
        @if (filled($hint))
            <span class="ss-switch__hint" id="{{ $id }}-hint">{{ $hint }}</span>
        @endif
    </span>
    <input {{ $attributes->merge(['type' => 'checkbox', 'role' => 'switch', 'id' => $id]) }} @checked($checked) @if (filled($hint)) aria-describedby="{{ $id }}-hint" @endif>
    <span class="ss-switch__track" aria-hidden="true"></span>
</label>
