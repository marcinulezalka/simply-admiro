{{-- Krok PIN logowania strefy. $zone: ZoneDefinition (Core), $pinLength: Auth\PinLength (pełny zakres: istniejący PIN mógł powstać przy innym zakresie strefy). --}}
<x-mainboard::auth.layout :title="text('sign_in.pin_title')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.pin.store')) }}">
        @csrf
        <div class="ss-field">
            <label class="ss-field__label" for="pin">{{ text('sign_in.pin') }}</label>
            <input class="ss-input ss-input--code" id="pin" name="pin" type="password" inputmode="numeric" autocomplete="current-password" minlength="{{ $pinLength->min }}" maxlength="{{ $pinLength->max }}" required autofocus @error('pin') aria-invalid="true" aria-describedby="pin-error" @enderror>
            @error('pin') <p class="ss-field__error" id="pin-error" role="alert">{{ $message }}</p> @enderror
        </div>
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ text('sign_in.continue') }}<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
    </form>

    <x-slot:actions>
        <a class="ss-link" href="{{ route($zone->routeName('auth.forgot-pin')) }}">{{ text('sign_in.forgot_pin_link') }}</a>
    </x-slot:actions>
</x-mainboard::auth.layout>
