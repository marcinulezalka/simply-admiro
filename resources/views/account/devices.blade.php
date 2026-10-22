{{-- Zaufane urządzenia konta. $devices: list<Auth\Devices\TrustedDeviceRecord>, $currentSelector: ?string (bieżąca przeglądarka); $zone daje Core. --}}
<x-mainboard::zone.layout :title="text('account.devices_title')">
    @if ($devices === [])
        <p>{{ text('account.devices_empty') }}</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ text('account.device') }}</th>
                    <th scope="col">{{ text('account.device_last_used') }}</th>
                    <th scope="col">{{ text('account.device_expires') }}</th>
                    <th scope="col">{{ text_action('delete') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($devices as $device)
                    <tr>
                        <td>
                            {{ $device->name }}
                            @if ($device->selector === $currentSelector) ({{ text('account.this_device') }}) @endif
                        </td>
                        <td>{{ local_date($device->lastUsedAt ?? $device->createdAt) }}</td>
                        <td>{{ local_date($device->expiresAt) }}</td>
                        <td>
                            <form method="POST" action="{{ route($zone->routeName('account.devices.revoke'), ['device' => $device->token]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">{{ text('account.device_revoke') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form method="POST" action="{{ route($zone->routeName('account.devices.revoke-all')) }}">
            @csrf
            @method('DELETE')
            <button type="submit">{{ text('account.devices_revoke_all') }}</button>
        </form>
    @endif
</x-mainboard::zone.layout>
