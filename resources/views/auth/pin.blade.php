{{-- Krok PIN logowania strefy (teksty przez $zone->signInText(): tytuł, wstęp, notka). $zone: ZoneDefinition (Core), $pinLength: Auth\PinLength (pełny zakres: istniejący PIN mógł powstać przy innym zakresie strefy). Pole z klawiaturą ekranową w formularzu (x-admiro::pin-field keypad-inline: przycisk w polu rozwija klawisze między polem a przyciskiem); przycisk wysłania z ikoną Entera jak „Zatwierdź PIN” klawiatury; „Nie pamiętam PIN-u” jako przycisk drugorzędny z ikoną. $keypadOpen: bool — klawiatura otwarta od razu (wariant podglądu w design systemie; domyślnie false). --}}
<x-admiro::auth.layout :title="$zone->signInText('pin_title')" :lead="$zone->signInText('pin_lead')" :note="$zone->signInText('pin_note')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.pin.store')) }}">
        @csrf
        <x-admiro::pin-field icon="lock" id="pin" name="pin" :label="$zone->signInText('pin')" autocomplete="current-password" :min="$pinLength->min" :max="$pinLength->max" :keypad-inline="true" :keypad-open="$keypadOpen ?? false" autofocus />
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ $zone->signInText('continue') }}<x-admiro::icon name="corner-down-left" class="ss-button__icon" /></button>
    </form>

    <x-slot:actions>
        <a class="ss-button" href="{{ route($zone->routeName('auth.forgot-pin')) }}"><x-admiro::icon name="help-circle" class="ss-button__icon" />{{ $zone->signInText('forgot_pin_link') }}</a>
    </x-slot:actions>
</x-admiro::auth.layout>
