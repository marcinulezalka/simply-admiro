{{-- Pole PIN-u (i innych kodów z cyfr) z etykietą, błędem walidacji i wirtualną klawiaturą numeryczną: przycisk z ikoną klawiatury w polu otwiera modal (<dialog> nazwany etykietą pola, js/pin-keypad.js) w szerokości treści i w kolorach karty formularza logowania (tło surface-muted, ramka border-strong): u góry pole z kropkami wpisanych cyfr, pod nim siatka smukłych, małych klawiszy w wysokości przycisków z ikoną (1–9, w dolnym rzędzie wyczyść, 0 na środku i usuwanie ostatniej cyfry w prawym dolnym rogu), a na dole „Wstecz” (zamyka okno) i „Zatwierdź PIN”, które zamyka okno i od razu wysyła formularz (gdy jest poprawny; inaczej fokus trafia do pierwszego niepoprawnego pola, np. powtórzenia PIN-u). Cyfry trafiają do pola na bieżąco, fizyczna klawiatura działa w polu i w oknie. Bez skryptu przycisk jest ukryty i zostaje zwykłe pole. id, name, label, autocomplete, min, max: wymagane; type: password (domyślnie) albo text; keypad: false = bez klawiatury; keypadOpen: true = klawiatura otwiera się od razu po załadowaniu (podgląd w design systemie); icon: nazwa subtelnej ikony w polu po lewej (np. lock); error: komunikat błędu pola (np. $errors->first('pin')); reszta atrybutów trafia na input (np. autofocus). --}}
@props(['id', 'name', 'label', 'autocomplete', 'min', 'max', 'type' => 'password', 'keypad' => true, 'keypadOpen' => false, 'icon' => null, 'error' => null])
<div class="ss-field">
    <label class="ss-field__label" for="{{ $id }}">{{ $label }}</label>
    <div @class(['ss-input-wrap', 'ss-input-wrap--icon' => $icon !== null, 'ss-input-wrap--keypad' => $keypad])>
        @if ($icon !== null)
            <x-admiro::icon :name="$icon" class="ss-input-wrap__icon" />
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
                <x-admiro::icon name="keypad" class="ss-input-wrap__action-icon" />
                <span class="ss-sr-only">{{ text('layout.keypad.open') }}</span>
            </button>
        @endif
    </div>
    @if (filled($error))
        <p class="ss-field__error" id="{{ $id }}-error" role="alert">{{ $error }}</p>
    @endif
</div>
@if ($keypad)
    <dialog class="ss-keypad" id="{{ $id }}-keypad" aria-label="{{ $label }}" data-ss-keypad data-ss-keypad-for="{{ $id }}"{{ $keypadOpen ? ' data-ss-keypad-show' : '' }}>
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
                <x-admiro::icon name="backspace" class="ss-keypad__key-icon" />
                <span class="ss-sr-only">{{ text('layout.keypad.backspace') }}</span>
            </button>
        </div>
        <div class="ss-keypad__footer">
            <button class="ss-button" type="button" data-ss-keypad-close><x-admiro::icon name="arrow-left" class="ss-button__icon" />{{ text('layout.keypad.back') }}</button>
            <button class="ss-button ss-button--primary ss-keypad__done" type="button" data-ss-keypad-done><x-admiro::icon name="check" class="ss-button__icon" />{{ text('layout.keypad.done') }}</button>
        </div>
    </dialog>
@endif
