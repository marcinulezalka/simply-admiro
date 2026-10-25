{{-- Krok PIN logowania strefy (teksty przez $zone->signInText(): tytuł, wstęp, notka). $zone: ZoneDefinition (Core), $pinLength: Auth\PinLength (pełny zakres: istniejący PIN mógł powstać przy innym zakresie strefy). Pole z klawiaturą ekranową (x-mainboard::pin-field). --}}
<x-mainboard::auth.layout :title="$zone->signInText('pin_title')" :lead="$zone->signInText('pin_lead')" :note="$zone->signInText('pin_note')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.pin.store')) }}">
        @csrf
        <x-mainboard::pin-field icon="lock" id="pin" name="pin" :error="$errors->first('pin')" :label="$zone->signInText('pin')" autocomplete="current-password" :min="$pinLength->min" :max="$pinLength->max" autofocus />
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ $zone->signInText('continue') }}<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
    </form>

    <x-slot:actions>
        <a class="ss-link" href="{{ route($zone->routeName('auth.forgot-pin')) }}">{{ $zone->signInText('forgot_pin_link') }}</a>
    </x-slot:actions>
</x-mainboard::auth.layout>
