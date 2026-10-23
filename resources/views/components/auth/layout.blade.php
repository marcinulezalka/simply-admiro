{{-- Układ stron logowania stref na szablonie bazowym x-mainboard::page (docs/design-system.md): karta na środku ekranu z logo, tytułem, opcjonalnym wstępem (lead) i komunikatem stanu; slot actions — akcje drugorzędne pod formularzem. Strony logowania nie są indeksowane. --}}
@props(['title' => null, 'description' => null, 'lead' => null])
@php($pageTitle = $title ?? text('sign_in.title'))
<x-mainboard::page :title="$pageTitle" :description="$description" robots="noindex, nofollow">
    <x-slot:head>
        <x-mainboard::styles />
    </x-slot:head>

    <div class="ss-auth">
        <main class="ss-auth__card">
            <x-mainboard::logo class="ss-auth__logo" />

            <div class="ss-auth__header">
                <h1 class="ss-auth__title">{{ $pageTitle }}</h1>
                @if (filled($lead))
                    <p class="ss-auth__lead">{{ $lead }}</p>
                @endif
            </div>

            @if (session('status'))
                <p class="ss-notice" role="status">{{ session('status') }}</p>
            @endif

            {{ $slot }}

            @isset($actions)
                <div class="ss-auth__actions">{{ $actions }}</div>
            @endisset
        </main>
    </div>
</x-mainboard::page>
