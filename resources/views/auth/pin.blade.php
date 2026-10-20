<x-mainboard::auth.layout :title="text('sign_in.pin_title')">
    <form method="POST" action="{{ route($zone->routeName('auth.pin.store')) }}">
        @csrf
        <label for="pin">{{ text('sign_in.pin') }}</label>
        <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="current-password" maxlength="{{ $pinLength }}" required autofocus>
        @error('pin') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit">{{ text('sign_in.continue') }}</button>
    </form>
</x-mainboard::auth.layout>
