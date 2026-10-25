{{-- Ustawienie PIN-u przy pierwszym logowaniu (teksty przez $zone->signInText(), z notką pod formularzem). $zone: ZoneDefinition (Core), $pinLength: Auth\PinLength (zakres strefy). Pola z klawiaturą ekranową (x-mainboard::pin-field). --}}
<x-mainboard::auth.layout :title="$zone->signInText('pin_setup_title')" :lead="$zone->signInText('pin_setup_intro', ['min' => $pinLength->min, 'max' => $pinLength->max])" :note="$zone->signInText('pin_setup_note')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.pin-setup.store')) }}">
        @csrf
        <x-mainboard::pin-field icon="lock" id="pin" name="pin" :error="$errors->first('pin')" :label="$zone->signInText('pin')" autocomplete="new-password" :min="$pinLength->min" :max="$pinLength->max" autofocus />
        <x-mainboard::pin-field icon="lock" id="pin_confirmation" name="pin_confirmation" :error="$errors->first('pin_confirmation')" :label="$zone->signInText('pin_confirmation')" autocomplete="new-password" :min="$pinLength->min" :max="$pinLength->max" />
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ $zone->signInText('pin_setup_submit') }}<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
    </form>
</x-mainboard::auth.layout>
