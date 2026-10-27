{{-- Przełącznik włącz/wyłącz (.ss-switch): checkbox z role="switch", opis i podpowiedź po lewej, tor po prawej. id: identyfikator pola; label, hint: teksty; checked: stan; hideLabel: etykieta tylko dla czytników ekranu (np. przełącznik w wierszu tabeli); icon: ikona przed etykietą; states: podpis stanu ON / OFF po lewej od toru (zmienia się z zaznaczeniem, tylko wizualnie — stan podaje rola switch); knob: wariant z kolorową gałką i napisem w niej — włączony: zielona gałka z ON po prawej, wyłączony: czerwona gałka z OFF po lewej (.ss-switch--knob, napisy z layout.switch_on / switch_off); class: dodatkowa klasa etykiety; reszta atrybutów trafia na input (np. name, disabled). --}}
@props(['id', 'label', 'hint' => null, 'checked' => false, 'hideLabel' => false, 'icon' => null, 'class' => null, 'states' => false, 'knob' => false])
<label @class(['ss-switch', 'ss-switch--knob' => $knob, (string) $class => filled($class)]) for="{{ $id }}">
    <span class="ss-switch__text">
        <span @class(['ss-sr-only' => $hideLabel, 'ss-switch__label' => $icon !== null])>@if ($icon !== null)<x-admiro::icon :name="$icon" class="ss-switch__icon" />@endif{{ $label }}</span>
        @if (filled($hint))
            <span class="ss-switch__hint" id="{{ $id }}-hint">{{ $hint }}</span>
        @endif
    </span>
    @if ($states)
        <span class="ss-switch__state" aria-hidden="true"><span class="ss-switch__state-on">{{ text('layout.switch_on') }}</span><span class="ss-switch__state-off">{{ text('layout.switch_off') }}</span></span>
    @endif
    <input {{ $attributes->merge(['type' => 'checkbox', 'role' => 'switch', 'id' => $id]) }} @checked($checked) @if (filled($hint)) aria-describedby="{{ $id }}-hint" @endif>
    <span class="ss-switch__track" aria-hidden="true" @if ($knob) data-on="{{ text('layout.switch_on') }}" data-off="{{ text('layout.switch_off') }}" @endif></span>
</label>
