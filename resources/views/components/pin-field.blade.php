{{-- Pole PIN-u (i innych kodów z cyfr) z etykietą, błędem walidacji i wirtualną klawiaturą numeryczną: przycisk z ikoną klawiatury w polu otwiera modal (<dialog>, js/pin-keypad.js) z cyframi 0–9, usuwaniem ostatniej cyfry, czyszczeniem i „Gotowe”; cyfry trafiają do pola na bieżąco, kropki pokazują ich liczbę, fizyczna klawiatura działa w polu i w oknie. Bez skryptu przycisk jest ukryty i zostaje zwykłe pole. id, name, label, autocomplete, min, max: wymagane; type: password (domyślnie) albo text (kod z e-maila); keypad: false = bez klawiatury; icon: nazwa subtelnej ikony w polu po lewej (np. lock); error: komunikat błędu pola (np. $errors->first('pin')); reszta atrybutów trafia na input (np. autofocus). --}}
@props(['id', 'name', 'label', 'autocomplete', 'min', 'max', 'type' => 'password', 'keypad' => true, 'icon' => null, 'error' => null])
<div class="ss-field">
    <label class="ss-field__label" for="{{ $id }}">{{ $label }}</label>
    <div @class(['ss-input-wrap', 'ss-input-wrap--icon' => $icon !== null, 'ss-input-wrap--keypad' => $keypad])>
        @if ($icon !== null)
            <x-mainboard::icon :name="$icon" class="ss-input-wrap__icon" />
        @endif
        <input {{ $attributes->merge(array_filter([
            'class' => 'ss-input ss-input--code',
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'inputmode' => 'numeric',
            'autocomplete' => $autocomplete,
            'minlength' => $min,
            'maxlength' => $max,
            'required' => true,
            'aria-invalid' => filled($error) ? 'true' : null,
            'aria-describedby' => filled($error) ? $id . '-error' : null,
        ], static fn (mixed $value): bool => $value !== null)) }}>
        @if ($keypad)
            <button class="ss-input-wrap__action" type="button" aria-haspopup="dialog" aria-controls="{{ $id }}-keypad" title="{{ text('layout.keypad.open') }}" data-ss-keypad-open hidden>
                <x-mainboard::icon name="keypad" class="ss-input-wrap__action-icon" />
                <span class="ss-sr-only">{{ text('layout.keypad.open') }}</span>
            </button>
        @endif
    </div>
    @if (filled($error))
        <p class="ss-field__error" id="{{ $id }}-error" role="alert">{{ $error }}</p>
    @endif
</div>
@if ($keypad)
    <dialog class="ss-keypad" id="{{ $id }}-keypad" aria-labelledby="{{ $id }}-keypad-title" data-ss-keypad data-ss-keypad-for="{{ $id }}">
        <div class="ss-keypad__header">
            <h2 class="ss-keypad__title" id="{{ $id }}-keypad-title">{{ $label }}</h2>
            <button class="ss-icon-button ss-keypad__close" type="button" data-ss-keypad-close>
                <x-mainboard::icon name="close" class="ss-icon-button__icon" />
                <span class="ss-sr-only">{{ text('layout.close') }}</span>
            </button>
        </div>
        <p class="ss-keypad__display" aria-live="polite" data-ss-keypad-display data-ss-keypad-count="{{ text('layout.keypad.count') }}"></p>
        <div class="ss-keypad__keys" role="group" aria-label="{{ text('layout.keypad.digits') }}">
            @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $digit)
                <button class="ss-keypad__key" type="button" data-ss-keypad-digit="{{ $digit }}">{{ $digit }}</button>
            @endforeach
            <button class="ss-keypad__key ss-keypad__key--muted" type="button" data-ss-keypad-clear>
                <span aria-hidden="true">C</span>
                <span class="ss-sr-only">{{ text('layout.keypad.clear') }}</span>
            </button>
            <button class="ss-keypad__key" type="button" data-ss-keypad-digit="0">0</button>
            <button class="ss-keypad__key ss-keypad__key--muted" type="button" data-ss-keypad-backspace>
                <x-mainboard::icon name="backspace" class="ss-keypad__key-icon" />
                <span class="ss-sr-only">{{ text('layout.keypad.backspace') }}</span>
            </button>
        </div>
        <button class="ss-button ss-button--primary ss-button--block" type="button" data-ss-keypad-done>{{ text('layout.keypad.done') }}</button>
    </dialog>
@endif
