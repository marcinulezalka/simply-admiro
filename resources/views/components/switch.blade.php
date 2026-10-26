{{-- Przełącznik włącz/wyłącz (.ss-switch): checkbox z role="switch", opis i podpowiedź po lewej, tor po prawej. id: identyfikator pola; label, hint: teksty; checked: stan; hideLabel: etykieta tylko dla czytników ekranu (np. przełącznik w wierszu tabeli); reszta atrybutów trafia na input (np. name, disabled). --}}
@props(['id', 'label', 'hint' => null, 'checked' => false, 'hideLabel' => false])
<label class="ss-switch" for="{{ $id }}">
    <span class="ss-switch__text">
        <span @class(['ss-sr-only' => $hideLabel])>{{ $label }}</span>
        @if (filled($hint))
            <span class="ss-switch__hint" id="{{ $id }}-hint">{{ $hint }}</span>
        @endif
    </span>
    <input {{ $attributes->merge(['type' => 'checkbox', 'role' => 'switch', 'id' => $id]) }} @checked($checked) @if (filled($hint)) aria-describedby="{{ $id }}-hint" @endif>
    <span class="ss-switch__track" aria-hidden="true"></span>
</label>
