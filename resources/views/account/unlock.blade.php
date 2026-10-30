{{-- Zablokowany ekran (ScreenLockController::show, jak Win+L): layout logowania strefy z nazwą konta w tytule, polem PIN-u z klawiaturą ekranową (x-admiro::pin-field keypad-inline) i „Odblokuj”, pod spodem wylogowanie (formularz POST). Treść aplikacji jest niewidoczna do podania poprawnego PIN-u. $zone: ZoneDefinition (Core), $name: string (nazwa konta), $pinLength: Auth\PinLength. --}}
<x-admiro::auth.layout :title="text('account.screen_lock.title')" :lead="text('account.screen_lock.lead', ['name' => $name])">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName(\Simplysmart\Admiro\Http\AccountRoutes::UNLOCK_STORE)) }}">
        @csrf
        <x-admiro::pin-field icon="lock-screen" id="pin" name="pin" :label="text('account.screen_lock.pin')" autocomplete="current-password" :min="$pinLength->min" :max="$pinLength->max" :keypad-inline="true" autofocus />
        <div class="ss-auth__submit">
            <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ text('account.screen_lock.submit') }}<x-admiro::icon name="corner-down-left" class="ss-button__icon" /></button>
        </div>
    </form>
    <form class="ss-auth__secondary" method="POST" action="{{ route($zone->routeName('auth.logout')) }}">
        @csrf
        <button class="ss-link" type="submit">{{ text('account.screen_lock.sign_out') }}</button>
    </form>
</x-admiro::auth.layout>
