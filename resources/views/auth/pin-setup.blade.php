<x-mainboard::auth.layout :title="text('sign_in.pin_setup_title')">
    <p>{{ text('sign_in.pin_setup_intro', ['length' => $pinLength]) }}</p>

    <form method="POST" action="{{ route($zone->routeName('auth.pin-setup.store')) }}">
        @csrf
        <label for="pin">{{ text('sign_in.pin') }}</label>
        <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="new-password" maxlength="{{ $pinLength }}" required autofocus>
        @error('pin') <p role="alert">{{ $message }}</p> @enderror
        <label for="pin_confirmation">{{ text('sign_in.pin_confirmation') }}</label>
        <input id="pin_confirmation" name="pin_confirmation" type="password" inputmode="numeric" autocomplete="new-password" maxlength="{{ $pinLength }}" required>
        <button type="submit">{{ text('sign_in.continue') }}</button>
    </form>
</x-mainboard::auth.layout>
