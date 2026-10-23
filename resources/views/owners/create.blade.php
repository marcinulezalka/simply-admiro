{{-- Założenie właściciela strefy. $zones: list<ZoneDefinition> (strefy z ZoneOwnerProvisioner), $organizations: Collection<Organization> (aktywne). --}}
<x-mainboard::zone.layout :title="text('admiro::admin.owner_title')">
    @if ($zones === [])
        <p>{{ text('admiro::admin.no_provisioners') }}</p>
    @else
        <p>{{ text('admiro::admin.owner_intro') }}</p>

        <form method="POST" action="{{ route('admiro.owners.store') }}">
            @csrf
            <label for="zone">{{ text('admiro::admin.zone') }}</label>
            <select id="zone" name="zone" required>
                @foreach ($zones as $ownerZone)
                    <option value="{{ $ownerZone->name }}" @selected(old('zone') === $ownerZone->name)>{{ $ownerZone->name }}{{ $ownerZone->teams ? ' (' . text('admiro::admin.zone_teams') . ')' : '' }}</option>
                @endforeach
            </select>
            @error('zone') <p role="alert">{{ $message }}</p> @enderror

            <label for="organization">{{ text('admiro::admin.organization') }}</label>
            <select id="organization" name="organization">
                <option value="">{{ text('admiro::admin.organization_operator') }}</option>
                @foreach ($organizations as $organization)
                    <option value="{{ $organization->token }}" @selected(old('organization') === $organization->token)>{{ $organization->name }}</option>
                @endforeach
            </select>
            @error('organization') <p role="alert">{{ $message }}</p> @enderror

            <label for="name">{{ text('admiro::admin.account_name') }}</label>
            <input id="name" name="name" value="{{ old('name') }}" required maxlength="255">
            @error('name') <p role="alert">{{ $message }}</p> @enderror

            <label for="email">{{ text('admiro::admin.account_email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255">
            @error('email') <p role="alert">{{ $message }}</p> @enderror

            <button type="submit">{{ text_action('create') }}</button>
        </form>
    @endif
</x-mainboard::zone.layout>
