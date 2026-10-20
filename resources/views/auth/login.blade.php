<x-mainboard::auth.layout :title="text('sign_in.title')">
    <form method="POST" action="{{ route($zone->routeName('auth.login.store')) }}">
        @csrf
        <label for="email">{{ text('sign_in.email') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        @error('email') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit">{{ text('sign_in.continue') }}</button>
    </form>
</x-mainboard::auth.layout>
