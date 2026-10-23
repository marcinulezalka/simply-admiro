{{-- Członkowie organizacji we wszystkich strefach. $organization: Organization, $members: list<array{zone: ZoneDefinition, account: ZoneAccount, roles: list<string>}>, $canManage: bool. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.members_title', ['organization' => $organization->name])">
    @error('member') <p role="alert">{{ $message }}</p> @enderror

    <table>
        <thead>
            <tr>
                <th scope="col">{{ text('admiro::admin.zone') }}</th>
                <th scope="col">{{ text('admiro::admin.account_name') }}</th>
                <th scope="col">{{ text('admiro::admin.account_email') }}</th>
                <th scope="col">{{ text('admiro::admin.roles') }}</th>
                @if ($canManage)
                    <th scope="col">{{ text('admiro::admin.account_actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($members as $member)
                <tr>
                    <td>{{ $member['zone']->name }}</td>
                    <td>{{ $member['account']->displayName() }}</td>
                    <td>{{ $member['account']->emailAddress() }}</td>
                    <td>{{ $member['roles'] === [] ? '—' : implode(', ', $member['roles']) }}</td>
                    @if ($canManage)
                        <td>
                            <form method="POST" action="{{ route('admiro.organizations.members.remove', ['organization' => $organization->token, 'managedZone' => $member['zone']->name, 'account' => $member['account']->getRouteKey()]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">{{ text('admiro::admin.member_remove') }}</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $canManage ? 5 : 4 }}">{{ text('admiro::admin.no_members') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</x-mainboard::zone.layout>
