{{-- Zmiana PIN-u zalogowanego konta. $pinLength (Auth\PinLength, zakres strefy dla nowego PIN-u), $currentPinLength (pełny zakres dla obecnego) i $codeLength (int) z ustawień strefy; $zone daje Core. --}}
<x-mainboard::zone.layout :title="text('account.pin_title')">
    <p>{{ text('account.pin_intro', ['min' => $pinLength->min, 'max' => $pinLength->max]) }}</p>

    <form method="POST" action="{{ route($zone->routeName('account.pin.code')) }}">
        @csrf
        <button type="submit">{{ text('account.send_code') }}</button>
    </form>

    <form method="POST" action="{{ route($zone->routeName('account.pin.update')) }}">
        @csrf
        @method('PUT')
        <label for="current_pin">{{ text('account.current_pin') }}</label>
        <input id="current_pin" name="current_pin" type="password" inputmode="numeric" autocomplete="current-password" minlength="{{ $currentPinLength->min }}" maxlength="{{ $currentPinLength->max }}" required>
        @error('current_pin') <p role="alert">{{ $message }}</p> @enderror

        <label for="code">{{ text('sign_in.code') }}</label>
        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="{{ $codeLength }}" required>
        @error('code') <p role="alert">{{ $message }}</p> @enderror

        <label for="pin">{{ text('account.new_pin') }}</label>
        <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="new-password" minlength="{{ $pinLength->min }}" maxlength="{{ $pinLength->max }}" required>
        @error('pin') <p role="alert">{{ $message }}</p> @enderror

        <label for="pin_confirmation">{{ text('sign_in.pin_confirmation') }}</label>
        <input id="pin_confirmation" name="pin_confirmation" type="password" inputmode="numeric" autocomplete="new-password" minlength="{{ $pinLength->min }}" maxlength="{{ $pinLength->max }}" required>

        <button type="submit">{{ text_action('save') }}</button>
    </form>
</x-mainboard::zone.layout>
