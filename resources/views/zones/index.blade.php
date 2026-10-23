{{-- Strefy z kontami: nazwa (odnośnik do kont), adres i organizacje klientów. $zones: list<Simplysmart\Mainboard\Zones\ZoneDefinition>. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.zones_title')">
    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
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
                            <th scope="row"><a class="ss-link" href="{{ route('admiro.zones.accounts', ['managedZone' => $managedZone->name]) }}">{{ $managedZone->name }}</a></th>
                            <td class="ss-table__number">/{{ $managedZone->slug }}</td>
                            <td><span class="ss-badge">{{ $managedZone->teams ? text('admiro::admin.yes') : text('admiro::admin.no') }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-mainboard::zone.layout>
