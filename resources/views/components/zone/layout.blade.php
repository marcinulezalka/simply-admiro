{{-- Układ stron strefy na szablonie bazowym x-mainboard::page (meta przekazywane dalej; strefa z logowaniem nie jest indeksowana). $zone i $account (konto zalogowane w strefie albo null) daje Core. --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null])
<x-mainboard::page :title="$title" :description="$description" :keywords="$keywords" :tags="$tags" :robots="$zone->requiresLogin() ? 'noindex, nofollow' : null">
    @if ($account !== null)
        <header>
            <p>{{ text('sign_in.signed_in_as', ['name' => $account->displayName()]) }}</p>
            <form method="POST" action="{{ route($zone->routeName('auth.logout')) }}">
                @csrf
                <button type="submit">{{ text('sign_in.sign_out') }}</button>
            </form>
        </header>
    @endif
    <main>
        <h1>{{ $title }}</h1>

        {{ $slot }}
    </main>
</x-mainboard::page>
