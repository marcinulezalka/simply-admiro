{{-- Organizacje. $organizations: Collection<Organization> (także wyłączone), $names: array<int, string> (id → nazwa, dla organizacji nadrzędnej), $canManage: bool. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.organizations_title')">
    @error('organization') <p role="alert">{{ $message }}</p> @enderror

    @if ($canManage)
        <p><a href="{{ route('admiro.organizations.create') }}">{{ text('admiro::admin.organization_create') }}</a></p>
    @endif

    <table>
        <thead>
            <tr>
                <th scope="col">{{ text('admiro::admin.organization') }}</th>
                <th scope="col">{{ text('admiro::admin.organization_parent') }}</th>
                <th scope="col">{{ text('admiro::admin.account_state') }}</th>
                <th scope="col">{{ text('admiro::admin.account_actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($organizations as $organization)
                <tr>
                    <td>{{ $organization->name }}</td>
                    <td>{{ $names[$organization->parent_id] ?? '—' }}</td>
                    <td>{{ $organization->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}</td>
                    <td>
                        <a href="{{ route('admiro.organizations.members', ['organization' => $organization->token]) }}">{{ text('admiro::admin.members') }}</a>
                        @if ($canManage)
                            <a href="{{ route('admiro.organizations.edit', ['organization' => $organization->token]) }}">{{ text_action('edit') }}</a>
                            @if ($organization->id !== \Simplysmart\Mainboard\Models\Organization::OPERATOR_ID)
                                <form method="POST" action="{{ route('admiro.organizations.toggle', ['organization' => $organization->token]) }}">
                                    @csrf
                                    <button type="submit">{{ $organization->is_active ? text('admiro::admin.action.deactivate') : text('admiro::admin.organization_enable') }}</button>
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-mainboard::zone.layout>
