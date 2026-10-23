{{-- Gotowy widok: logowanie strefy na prawdziwym layoucie x-mainboard::auth.layout w wariancie widoku ($view->authLayout(): jedna karta albo dwie obok siebie), bez strefy i bez wysyłania. $view: DesignSystemView. --}}
<x-mainboard::auth.layout :title="text('sign_in.title')" :lead="text('design_system.demo.sign_in_lead')" :layout="$view->authLayout()">
    <div class="ss-form">
        <x-mainboard::input id="demo-signin-email" :label="text('sign_in.email')" icon="mail" type="email" autocomplete="email" value="anna@example.com" />
        <button class="ss-button ss-button--primary ss-button--block" type="button">{{ text('sign_in.continue') }}<x-mainboard::icon name="arrow-right" class="ss-button__icon" /></button>
    </div>

    <x-slot:actions>
        <a class="ss-link" href="{{ route('mainboard.design-system') }}">{{ text('design_system.back') }}</a>
        <a class="ss-link" href="{{ route('mainboard.design-system.view', \Simplysmart\Mainboard\Enums\DesignSystemView::Dashboard) }}">{{ text('design_system.views.dashboard') }}</a>
    </x-slot:actions>
</x-mainboard::auth.layout>
