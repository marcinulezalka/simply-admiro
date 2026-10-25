{{-- Członkowie organizacji we wszystkich strefach: strefa, osoba (awatar, nazwa, adres), role strefy jako plakietki i usunięcie z organizacji (organizations.manage). $organization: Organization, $members: list<array{zone: ZoneDefinition, account: ZoneAccount, roles: list<string>}>, $canManage: bool. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.members_title', ['organization' => $organization->name])" :back="route('admiro.organizations.index')">
    @error('member') <p class="ss-notice" role="alert"><x-mainboard::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ text('admiro::admin.account_name') }}</th>
                        <th scope="col">{{ text('admiro::admin.zone') }}</th>
                        <th scope="col">{{ text('admiro::admin.roles') }}</th>
                        @if ($canManage)
                            <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <th scope="row">
                                <span class="ss-person">
                                    <x-mainboard::avatar :name="$member['account']->displayName()" :color="$member['account']->avatarColor()" size="sm" />
                                    <span class="ss-person__text"><span>{{ $member['account']->displayName() }}</span><span class="ss-muted">{{ $member['account']->emailAddress() }}</span></span>
                                </span>
                            </th>
                            <td><span class="ss-badge">{{ $member['zone']->name }}</span></td>
                            <td>
                                @forelse ($member['roles'] as $role)
                                    <span class="ss-badge">{{ $member['zone']->roleLabel($role) }}</span>
                                @empty
                                    <span class="ss-muted">—</span>
                                @endforelse
                            </td>
                            @if ($canManage)
                                <td>
                                    <form method="POST" action="{{ route('admiro.organizations.members.remove', ['organization' => $organization->token, 'managedZone' => $member['zone']->name, 'account' => $member['account']->getRouteKey()]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ss-button" type="submit">{{ text('admiro::admin.member_remove') }}</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ss-muted">{{ text('admiro::admin.no_members') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-mainboard::zone.layout>
