{{-- Strefy z kontami. $zones: list<Simplysmart\Mainboard\Zones\ZoneDefinition>. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.zones_title')">
    <table>
        <thead>
            <tr>
                <th scope="col">{{ text('admiro::admin.zone') }}</th>
                <th scope="col">{{ text('admiro::admin.zone_slug') }}</th>
                <th scope="col">{{ text('admiro::admin.zone_teams') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($zones as $managedZone)
                <tr>
                    <td><a href="{{ route('admiro.zones.accounts', ['managedZone' => $managedZone->name]) }}">{{ $managedZone->name }}</a></td>
                    <td>/{{ $managedZone->slug }}</td>
                    <td>{{ $managedZone->teams ? text('admiro::admin.yes') : text('admiro::admin.no') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-mainboard::zone.layout>
