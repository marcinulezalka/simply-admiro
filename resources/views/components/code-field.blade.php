{{-- Pole kodu z e-maila (np. kod logowania, kod resetu PIN-u): osobne pole na każdą cyfrę (.ss-code__cell, js/code-field.js). Serwer renderuje jedno zwykłe pole `name` (bez skryptu zostaje ono); skrypt pokazuje komórki, zamienia zwykłe pole na ukryte i składa w nim kod z komórek. Wpisanie cyfry przechodzi do następnej komórki, Backspace w pustej wraca do poprzedniej, wklejenie albo autouzupełnianie rozkłada cały kod na komórki. id, name, label, length: wymagane; icon: subtelna ikona zwykłego pola po lewej (np. shield-check); error: komunikat błędu pola; reszta atrybutów trafia na zwykłe pole (np. autofocus — skrypt przenosi go na pierwszą komórkę). --}}
@props(['id', 'name', 'label', 'length', 'icon' => null, 'error' => null])
<div class="ss-field" data-ss-code>
    <label class="ss-field__label" id="{{ $id }}-label" for="{{ $id }}" data-ss-code-label>{{ $label }}</label>
    <div @class(['ss-input-wrap', 'ss-input-wrap--icon' => $icon !== null]) data-ss-code-fallback>
        @if ($icon !== null)
            <x-admiro::icon :name="$icon" class="ss-input-wrap__icon" />
        @endif
        <input {{ $attributes->merge(array_filter([
            'class' => 'ss-input ss-input--code',
            'id' => $id,
            'name' => $name,
            'inputmode' => 'numeric',
            'autocomplete' => 'one-time-code',
            'minlength' => $length,
            'maxlength' => $length,
            'pattern' => '[0-9]{' . $length . '}',
            'required' => true,
            'aria-invalid' => filled($error) ? 'true' : null,
            'aria-describedby' => filled($error) ? $id . '-error' : null,
        ], static fn (mixed $value): bool => $value !== null)) }} data-ss-code-input>
    </div>
    <div class="ss-code" role="group" aria-labelledby="{{ $id }}-label" hidden data-ss-code-cells>
        @for ($position = 1; $position <= $length; $position++)
            <input class="ss-input ss-code__cell" id="{{ $id }}-{{ $position }}" type="text" inputmode="numeric" pattern="[0-9]" autocomplete="{{ $position === 1 ? 'one-time-code' : 'off' }}" aria-label="{{ text('layout.code_digit', ['position' => $position, 'length' => $length]) }}" @if (filled($error)) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif data-ss-code-cell>
        @endfor
    </div>
    @if (filled($error))
        <p class="ss-field__error" id="{{ $id }}-error" role="alert">{{ $error }}</p>
    @endif
</div>
