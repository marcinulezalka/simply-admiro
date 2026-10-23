{{-- Formularz adresu e-mail (start logowania strefy) z pułapką formularza. $zone: ZoneDefinition (Core). --}}
<x-mainboard::auth.layout :title="text('sign_in.title')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.login.store')) }}">
        @csrf
        <x-mainboard::honeypot />
        <x-mainboard::input id="email" :label="text('sign_in.email')" icon="mail" name="email" type="email" :value="old('email')" autocomplete="email" :placeholder="text('placeholders.email')" required autofocus :error="$errors->first('email')" />
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ text('sign_in.continue') }}<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
    </form>
</x-mainboard::auth.layout>
