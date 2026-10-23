{{-- Układ stron strefy na szablonie bazowym x-mainboard::page (meta przekazywane dalej; strefa z logowaniem nie jest indeksowana). $zone, $account (konto zalogowane w strefie albo null), $organizations (organizacje konta, gdy strefa ma teams i jest ich kilka, inaczej null) i $currentOrganizationId daje Core. --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null])
<x-mainboard::page :title="$title" :description="$description" :keywords="$keywords" :tags="$tags" :robots="$zone->requiresLogin() ? 'noindex, nofollow' : null">
    @if ($account !== null)
        <header>
            <p>{{ text('sign_in.signed_in_as', ['name' => $account->displayName()]) }}</p>
            <nav aria-label="{{ text('account.menu') }}">
                <a href="{{ route($zone->routeName('account.profile')) }}">{{ text('account.profile_title') }}</a>
                <a href="{{ route($zone->routeName('account.pin')) }}">{{ text('account.pin_title') }}</a>
                @if ($zone->auth()->trustedDevicesEnabled)
                    <a href="{{ route($zone->routeName('account.devices')) }}">{{ text('account.devices_title') }}</a>
                @endif
            </nav>
            @if ($organizations !== null)
                <form method="POST" action="{{ route($zone->routeName('account.organization.switch')) }}">
                    @csrf
                    <label for="organization-switch">{{ text('account.organization') }}</label>
                    <select id="organization-switch" name="organization">
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->token }}" @selected($organization->id === $currentOrganizationId)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit">{{ text('account.organization_switch') }}</button>
                </form>
            @endif
            <form method="POST" action="{{ route($zone->routeName('auth.logout')) }}">
                @csrf
                <button type="submit">{{ text('sign_in.sign_out') }}</button>
            </form>
        </header>
    @endif
    <main>
        <h1>{{ $title }}</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        {{ $slot }}
    </main>
</x-mainboard::page>
