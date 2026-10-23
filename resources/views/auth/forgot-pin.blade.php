{{-- „Nie pamiętam PIN-u” (trasa auth.forgot-pin, strona dla gości): PIN-u nie da się odzyskać, ustawia się go od nowa po resecie przez administratora strefy (Auth\AccountAdministration::resetPin usuwa PIN i odwołuje dostęp; nowy PIN po kodzie z e-maila przy najbliższym logowaniu). $zone: ZoneDefinition (Core). --}}
<x-mainboard::auth.layout :title="text('sign_in.forgot_pin.title')" :lead="text('sign_in.forgot_pin.lead')">
    <div class="ss-card ss-card--muted">
        <h2 class="ss-card__title">{{ text('sign_in.forgot_pin.steps_title') }}</h2>
        <ol class="ss-steps">
            @foreach (['admin', 'revoked', 'sign_in', 'set'] as $step)
                <li>{{ text('sign_in.forgot_pin.steps.' . $step) }}</li>
            @endforeach
        </ol>
    </div>

    <x-slot:actions>
        <a class="ss-button ss-button--primary ss-button--pill" href="{{ route($zone->routeName('auth.login')) }}"><x-mainboard::icon name="arrow-left" class="ss-button__icon" />{{ text('sign_in.forgot_pin.back') }}</a>
    </x-slot:actions>
</x-mainboard::auth.layout>
