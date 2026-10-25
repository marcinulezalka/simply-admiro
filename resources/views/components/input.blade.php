{{-- Pole formularza z etykietą, subtelną ikoną w tle po lewej, maską (Enums\InputMask: wzór, klawiatura, ikona, podpowiedź „np. …” w polu, format pod polem), podpowiedzią i błędem; własna podpowiedź w polu przez atrybut placeholder (teksty `placeholders.*`) (aria-invalid, aria-describedby). id, label: wymagane; icon: nazwa ikony (domyślnie z maski); mask: ?InputMask; hint, error: teksty; field-class: dodatkowe klasy kontenera pola (układ); reszta atrybutów trafia na input (name, type, value, autocomplete…). --}}
@props(['id', 'label', 'icon' => null, 'mask' => null, 'hint' => null, 'error' => null, 'fieldClass' => null])
@php
    $icon ??= $mask?->icon();
    $hint ??= $mask !== null ? text('layout.mask_format', ['format' => $mask->pattern()]) : null;
    $described = array_filter([filled($hint) ? $id . '-hint' : null, filled($error) ? $id . '-error' : null]);
@endphp
<div @class(['ss-field', $fieldClass])>
    <label class="ss-field__label" for="{{ $id }}">{{ $label }}</label>
    <div @class(['ss-input-wrap', 'ss-input-wrap--icon' => $icon !== null])>
        @if ($icon !== null)
            <x-mainboard::icon :name="$icon" class="ss-input-wrap__icon" />
        @endif
        <input {{ $attributes->merge(array_filter([
            'class' => 'ss-input',
            'id' => $id,
            'type' => 'text',
            'inputmode' => $mask?->inputMode(),
            'placeholder' => $mask?->placeholder(),
            'maxlength' => $mask !== null ? mb_strlen($mask->pattern()) : null,
            'data-ss-mask' => $mask?->pattern(),
            'aria-invalid' => filled($error) ? 'true' : null,
            'aria-describedby' => $described !== [] ? implode(' ', $described) : null,
        ], static fn (mixed $value): bool => $value !== null)) }}>
    </div>
    @if (filled($hint))
        <p class="ss-field__hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif
    @if (filled($error))
        <p class="ss-field__error" id="{{ $id }}-error" role="alert">{{ $error }}</p>
    @endif
</div>
