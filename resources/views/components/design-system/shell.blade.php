{{-- Powłoka gotowych widoków aplikacji (docs/design-system.md §5): pasek podglądu z nawigacją design systemu, top bar, nawigacja modułowa i nagłówek kontekstowy; treść widoku w slocie. $view: DesignSystemView; module: aktywny moduł (dashboard, invoices, clients); meta i back: opcjonalne; slot actions: akcje nagłówka (najwyżej jedna główna). --}}
@props(['view', 'title', 'module', 'meta' => null, 'back' => null])
@php
    use Simplysmart\Admiro\Enums\DesignSystemView;
    $modules = ['dashboard' => ['home', DesignSystemView::Dashboard], 'invoices' => ['invoice', DesignSystemView::List], 'clients' => ['users', DesignSystemView::Form]];
@endphp
<x-admiro::page :title="$title . ' · ' . text('design_system.title')" robots="noindex, nofollow">
    <x-slot:head>
        <x-admiro::styles :files="[...\Simplysmart\Admiro\View\CoreAssets::STYLESHEETS, 'css/design-system.css']" />
        <x-admiro::scripts :files="['js/input-mask.js']" />
    </x-slot:head>

    <div class="ss-ds-preview">
        <x-admiro::design-system.nav :current="$view->value" />

        <div class="ss-app">
            <header class="ss-topbar">
                <x-admiro::logo class="ss-topbar__logo" />
                <label class="ss-search">
                    <x-admiro::icon name="search" class="ss-search__icon" />
                    <span class="ss-sr-only">{{ text_action('search') }}</span>
                    <input class="ss-search__input" type="search" placeholder="{{ text('design_system.demo.search') }}">
                </label>
                <x-admiro::avatar :name="text('design_system.demo.account')" :accent="true" :label="true" />
            </header>

            <nav class="ss-module-nav" aria-label="{{ text('design_system.nav_views') }}">
                @foreach ($modules as $key => [$icon, $target])
                    <a class="ss-module-nav__link" href="{{ route('simplysmart.design-system.view', $target) }}" @if ($key === $module) aria-current="page" @endif>
                        <x-admiro::icon :name="$icon" class="ss-module-nav__icon" />
                        <span>{{ text('design_system.demo.modules.' . $key) }}</span>
                    </a>
                @endforeach
            </nav>

            <main class="ss-main">
                <x-admiro::app.context :title="$title" :meta="$meta" :back="$back">
                    @isset($actions)
                        <x-slot:actions>{{ $actions }}</x-slot:actions>
                    @endisset
                </x-admiro::app.context>

                {{ $slot }}
            </main>
        </div>
    </div>
</x-admiro::page>
