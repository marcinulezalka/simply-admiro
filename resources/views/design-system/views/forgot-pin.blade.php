{{-- Widok: „Nie pamiętam PIN-u” na layoucie logowania — PIN-u nie da się odzyskać, ustawia się go od nowa po resecie przez administratora (Auth\AccountAdministration::resetPin: usuwa PIN i odwołuje dostęp; nowy PIN przy logowaniu po kodzie z e-maila). $view: DesignSystemView. --}}
<x-mainboard::auth.layout :title="text('design_system.forgot_pin.title')" :lead="text('design_system.forgot_pin.lead')">
    <div class="ss-card ss-card--muted">
        <h2 class="ss-card__title">{{ text('design_system.forgot_pin.steps_title') }}</h2>
        <ol class="ss-steps">
            @foreach (['admin', 'revoked', 'sign_in', 'set'] as $step)
                <li>{{ text('design_system.forgot_pin.steps.' . $step) }}</li>
            @endforeach
        </ol>
    </div>

    <x-slot:actions>
        <a class="ss-button ss-button--primary ss-button--pill" href="{{ route('mainboard.design-system.view', \Simplysmart\Mainboard\Enums\DesignSystemView::SignIn) }}"><x-mainboard::icon name="arrow-left" class="ss-button__icon" />{{ text('design_system.forgot_pin.back') }}</a>
    </x-slot:actions>
</x-mainboard::auth.layout>
