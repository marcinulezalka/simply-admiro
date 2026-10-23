{{-- Pulpit administratora. $canViewZones, $canAssignOwners, $canManageAdministrators: bool (uprawnienia administratora). --}}
<x-mainboard::zone.layout :title="text('admiro::dashboard.title')">
    <nav aria-label="{{ text('admiro::dashboard.title') }}">
        <ul>
            @if ($canViewZones)
                <li><a href="{{ route('admiro.zones.index') }}">{{ text('admiro::admin.zones_title') }}</a></li>
            @endif
            @if ($canAssignOwners)
                <li><a href="{{ route('admiro.owners.create') }}">{{ text('admiro::admin.owner_title') }}</a></li>
            @endif
            @if ($canManageAdministrators)
                <li><a href="{{ route('admiro.administrators.index') }}">{{ text('admiro::admin.administrators_title') }}</a></li>
            @endif
        </ul>
    </nav>

    @if (! $canViewZones && ! $canAssignOwners && ! $canManageAdministrators)
        <p>{{ text('admiro::admin.no_sections') }}</p>
    @endif
</x-mainboard::zone.layout>
