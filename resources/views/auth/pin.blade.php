{{-- Krok PIN logowania strefy (teksty przez $zone->signInText(): tytuł, wstęp, notka). $zone: ZoneDefinition (Core), $pinLength: Auth\PinLength (pełny zakres: istniejący PIN mógł powstać przy innym zakresie strefy). Pole z klawiaturą ekranową w formularzu (x-admiro::pin-field keypad-inline: przycisk w polu rozwija klawisze między polem a przyciskiem); przycisk wysłania „Zatwierdź” (pin_submit) z ikoną Entera jak „Zatwierdź PIN” klawiatury; „Nie pamiętam PIN-u” jako link wyśrodkowany tuż pod nim (.ss-auth__submit). $keypadOpen: bool — klawiatura otwarta od razu (wariant podglądu w design systemie; domyślnie false). --}}
<x-admiro::auth.layout :title="$zone->signInText('pin_title')" :lead="$zone->signInText('pin_lead')" :note="$zone->signInText('pin_note')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.pin.store')) }}">
        @csrf
        <x-admiro::pin-field icon="lock" id="pin" name="pin" :label="$zone->signInText('pin')" autocomplete="current-password" :min="$pinLength->min" :max="$pinLength->max" :keypad-inline="true" :keypad-open="$keypadOpen ?? false" autofocus />
        <div class="ss-auth__submit">
            <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ $zone->signInText('pin_submit') }}<x-admiro::icon name="corner-down-left" class="ss-button__icon" /></button>
            <a class="ss-link" href="{{ route($zone->routeName('auth.forgot-pin')) }}">{{ $zone->signInText('forgot_pin_link') }}</a>
        </div>
    </form>
</x-admiro::auth.layout>
