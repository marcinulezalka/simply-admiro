{{-- „Nie pamiętam PIN-u” (ForgotPinController, strona dla gości): PIN-u nie da się odzyskać, ustawia się go od nowa. Konto z zapasowym adresem e-mail usuwa PIN samodzielnie (Auth\Recovery\RecoveryPinReset: adres konta → kod na zapasowy adres → PIN usunięty, sesje i zaufane urządzenia odwołane), bez niego — po resecie przez administratora strefy (Auth\AccountAdministration::resetPin); nowy PIN po kodzie z głównego adresu przy najbliższym logowaniu. Teksty przez $zone->signInText(). $codePending: bool (w sesji czeka kod resetu; domyślnie false, np. w podglądzie design systemu); $codeLength: int (domyślnie ze strefy); $zone: ZoneDefinition (Core). --}}
@php
    $routes = \Simplysmart\Mainboard\Http\SignInRoutes::class;
    $codePending ??= false;
    $codeLength ??= $zone->auth()->codeLength;
@endphp
<x-mainboard::auth.layout :title="$zone->signInText('forgot_pin.title')" :lead="$zone->signInText('forgot_pin.lead')">
    <div class="ss-card ss-card--muted">
        <h2 class="ss-card__title">{{ $zone->signInText('forgot_pin.recovery.title') }}</h2>
        @if ($codePending)
            <p class="ss-muted">{{ $zone->signInText('forgot_pin.recovery.code_lead') }}</p>
            <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.' . $routes::FORGOT_PIN_RESET)) }}">
                @csrf
                <x-mainboard::pin-field icon="shield-check" id="forgot-pin-code" name="code" :label="$zone->signInText('code')" type="text" autocomplete="one-time-code" :min="$codeLength" :max="$codeLength" :keypad="false" :error="$errors->first('code')" />
                <button class="ss-button ss-button--primary ss-button--pill" type="submit"><x-mainboard::icon name="lock" class="ss-button__icon" />{{ $zone->signInText('forgot_pin.recovery.reset') }}</button>
            </form>
            <form method="POST" action="{{ route($zone->routeName('auth.' . $routes::FORGOT_PIN_CANCEL)) }}">
                @csrf
                @method('DELETE')
                <button class="ss-button" type="submit">{{ $zone->signInText('forgot_pin.recovery.other_address') }}</button>
            </form>
        @else
            <p class="ss-muted">{{ $zone->signInText('forgot_pin.recovery.lead') }}</p>
            @error('code') <p class="ss-notice" role="alert"><x-mainboard::icon name="alert-triangle" /> {{ $message }}</p> @enderror
            <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.' . $routes::FORGOT_PIN_CODE)) }}">
                @csrf
                <x-mainboard::input id="forgot-pin-email" name="email" type="email" :label="$zone->signInText('email')" icon="mail" :value="old('email')" :placeholder="text('placeholders.email')" :error="$errors->first('email')" required maxlength="255" autocomplete="email" />
                <button class="ss-button ss-button--primary ss-button--pill" type="submit"><x-mainboard::icon name="mail" class="ss-button__icon" />{{ $zone->signInText('forgot_pin.recovery.send') }}</button>
            </form>
        @endif
    </div>

    <div class="ss-card ss-card--muted">
        <h2 class="ss-card__title">{{ $zone->signInText('forgot_pin.steps_title') }}</h2>
        <ol class="ss-steps">
            @foreach (['admin', 'revoked', 'sign_in', 'set'] as $step)
                <li>{{ $zone->signInText('forgot_pin.steps.' . $step) }}</li>
            @endforeach
        </ol>
    </div>

    <x-slot:actions>
        <a class="ss-button ss-button--primary ss-button--pill" href="{{ route($zone->routeName('auth.login')) }}"><x-mainboard::icon name="arrow-left" class="ss-button__icon" />{{ $zone->signInText('forgot_pin.back') }}</a>
    </x-slot:actions>
</x-mainboard::auth.layout>
