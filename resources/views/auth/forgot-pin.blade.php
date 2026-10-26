{{-- „Nie pamiętam PIN-u” (ForgotPinController, strona dla gości): PIN-u nie da się odzyskać, ustawia się go od nowa. Konto z zapasowym adresem e-mail usuwa PIN samodzielnie (Auth\Recovery\RecoveryPinReset: adres konta → kod na zapasowy adres → PIN usunięty, sesje i zaufane urządzenia odwołane), bez niego — po resecie przez administratora strefy (Auth\AccountAdministration::resetPin); nowy PIN po kodzie z głównego adresu przy najbliższym logowaniu. Widok albo/albo: po wejściu widać nagłówek-odnośnik „Masz zapasowy adres e-mail?” (<details>, .ss-forgot__recovery) i kroki bez zapasowego adresu; rozwinięcie nagłówka pokazuje formularz i ukrywa kroki (.ss-forgot:has(...[open])), zwinięcie przywraca kroki. Sekcja jest rozwinięta od razu, gdy czeka kod albo formularz ma błędy. Przyciski standardowe, powrót do logowania drugorzędny. Teksty przez $zone->signInText(). $codePending: bool (w sesji czeka kod resetu; domyślnie false, np. w podglądzie design systemu); $recoveryOpen: bool (sekcja zapasowego adresu rozwinięta od razu; wariant podglądu w design systemie, domyślnie false); $codeLength: int (domyślnie ze strefy); $zone: ZoneDefinition (Core). --}}
@php
    $routes = \Simplysmart\Admiro\Http\SignInRoutes::class;
    $codePending ??= false;
    $recoveryOpen ??= false;
    $codeLength ??= $zone->auth()->codeLength;
@endphp
<x-admiro::auth.layout :title="$zone->signInText('forgot_pin.title')" :lead="$zone->signInText('forgot_pin.lead')">
    <div class="ss-forgot">
        <details class="ss-forgot__recovery" @if ($codePending || $recoveryOpen || $errors->any()) open @endif>
            <summary class="ss-forgot__toggle"><x-admiro::icon name="mail" class="ss-forgot__toggle-icon" /><span>{{ $zone->signInText('forgot_pin.recovery.title') }}</span><x-admiro::icon name="chevron-down" class="ss-forgot__chevron" /></summary>
            <div class="ss-card ss-card--muted ss-forgot__body">
                @if ($codePending)
                    <p class="ss-muted">{{ $zone->signInText('forgot_pin.recovery.code_lead') }}</p>
                    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.' . $routes::FORGOT_PIN_RESET)) }}">
                        @csrf
                        <x-admiro::code-field id="forgot-pin-code" name="code" :label="$zone->signInText('code')" :length="$codeLength" icon="shield-check" />
                        <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="lock" class="ss-button__icon" />{{ $zone->signInText('forgot_pin.recovery.reset') }}</button>
                    </form>
                    <form method="POST" action="{{ route($zone->routeName('auth.' . $routes::FORGOT_PIN_CANCEL)) }}">
                        @csrf
                        @method('DELETE')
                        <button class="ss-button" type="submit"><x-admiro::icon name="mail" class="ss-button__icon" />{{ $zone->signInText('forgot_pin.recovery.other_address') }}</button>
                    </form>
                @else
                    <p class="ss-muted">{{ $zone->signInText('forgot_pin.recovery.lead') }}</p>
                    @error('code') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror
                    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.' . $routes::FORGOT_PIN_CODE)) }}">
                        @csrf
                        <x-admiro::input id="forgot-pin-email" name="email" type="email" :label="$zone->signInText('email')" icon="mail" :value="old('email')" :placeholder="text('placeholders.email')" required maxlength="255" autocomplete="email" />
                        <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="mail" class="ss-button__icon" />{{ $zone->signInText('forgot_pin.recovery.send') }}</button>
                    </form>
                @endif
            </div>
        </details>

        <div class="ss-card ss-card--muted ss-forgot__steps">
            <h2 class="ss-card__title">{{ $zone->signInText('forgot_pin.steps_title') }}</h2>
            <ol class="ss-steps">
                @foreach (['admin', 'revoked', 'sign_in', 'set'] as $step)
                    <li>{{ $zone->signInText('forgot_pin.steps.' . $step) }}</li>
                @endforeach
            </ol>
        </div>
    </div>

    <x-slot:actions>
        <a class="ss-button" href="{{ route($zone->routeName('auth.login')) }}"><x-admiro::icon name="arrow-left" class="ss-button__icon" />{{ $zone->signInText('forgot_pin.back') }}</a>
    </x-slot:actions>
</x-admiro::auth.layout>
