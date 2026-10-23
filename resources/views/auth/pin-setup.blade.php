{{-- Ustawienie PIN-u przy pierwszym logowaniu. $zone: ZoneDefinition (Core), $pinLength: int. --}}
<x-mainboard::auth.layout :title="text('sign_in.pin_setup_title')" :lead="text('sign_in.pin_setup_intro', ['length' => $pinLength])">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.pin-setup.store')) }}">
        @csrf
        <div class="ss-field">
            <label class="ss-field__label" for="pin">{{ text('sign_in.pin') }}</label>
            <input class="ss-input ss-input--code" id="pin" name="pin" type="password" inputmode="numeric" autocomplete="new-password" maxlength="{{ $pinLength }}" required autofocus @error('pin') aria-invalid="true" aria-describedby="pin-error" @enderror>
            @error('pin') <p class="ss-field__error" id="pin-error" role="alert">{{ $message }}</p> @enderror
        </div>
        <div class="ss-field">
            <label class="ss-field__label" for="pin_confirmation">{{ text('sign_in.pin_confirmation') }}</label>
            <input class="ss-input ss-input--code" id="pin_confirmation" name="pin_confirmation" type="password" inputmode="numeric" autocomplete="new-password" maxlength="{{ $pinLength }}" required>
        </div>
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ text('sign_in.continue') }}</button>
    </form>
</x-mainboard::auth.layout>
