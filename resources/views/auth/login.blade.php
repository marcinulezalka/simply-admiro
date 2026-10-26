{{-- Formularz adresu e-mail (start logowania strefy) z pułapką formularza; teksty przez $zone->signInText() (paczka strefy może je podmienić): nagłówek, wstęp, przykład adresu, przycisk i notka. $zone: ZoneDefinition (Core). --}}
@php($placeholder = $zone->signInText('email_placeholder'))
<x-admiro::auth.layout :title="$zone->signInText('title')" :lead="$zone->signInText('lead')" :note="$zone->signInText('note')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.login.store')) }}">
        @csrf
        <x-admiro::honeypot />
        <x-admiro::input id="email" :label="$zone->signInText('email')" icon="mail" name="email" type="email" :value="old('email')" autocomplete="email" :placeholder="$placeholder !== '' ? $placeholder : text('placeholders.email')" required autofocus />
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ $zone->signInText('submit') }}<x-admiro::icon name="arrow-right" class="ss-button__icon" /></button>
    </form>
</x-admiro::auth.layout>
