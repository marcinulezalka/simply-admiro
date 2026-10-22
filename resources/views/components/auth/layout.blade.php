{{-- Układ stron logowania stref na szablonie bazowym x-mainboard::page; strony logowania nie są indeksowane. --}}
@props(['title' => null, 'description' => null])
@php($pageTitle = $title ?? text('sign_in.title'))
<x-mainboard::page :title="$pageTitle" :description="$description" robots="noindex, nofollow">
    <main>
        <h1>{{ $pageTitle }}</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        {{ $slot }}
    </main>
</x-mainboard::page>
