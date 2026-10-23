{{-- Formularz adresu e-mail (start logowania strefy) z pułapką formularza. $zone: ZoneDefinition (Core). --}}
<x-mainboard::auth.layout :title="text('sign_in.title')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.login.store')) }}">
        @csrf
        <x-mainboard::honeypot />
        <div class="ss-field">
            <label class="ss-field__label" for="email">{{ text('sign_in.email') }}</label>
            <input class="ss-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email') <p class="ss-field__error" id="email-error" role="alert">{{ $message }}</p> @enderror
        </div>
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ text('sign_in.continue') }}</button>
    </form>
</x-mainboard::auth.layout>
