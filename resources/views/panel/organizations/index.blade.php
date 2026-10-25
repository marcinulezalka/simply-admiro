{{-- Organizacje (także wyłączone): nazwa, organizacja nadrzędna, stan jako plakietka i akcje (członkowie; z organizations.manage zmiana i włączenie / wyłączenie, organizacja operatora chroniona); „Nowa organizacja” w nagłówku. $organizations: Collection<Organization>, $names: array<int, string> (id → nazwa, dla organizacji nadrzędnej), $canManage: bool. --}}
<x-admiro::zone.layout :title="text('admiro::admin.organizations_title')">
    @if ($canManage)
        <x-slot:actions>
            <a class="ss-button ss-button--primary" href="{{ route('admiro.organizations.create') }}"><x-admiro::icon name="building" class="ss-button__icon" />{{ text('admiro::admin.organization_create') }}</a>
        </x-slot:actions>
    @endif

    @error('organization') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ text('admiro::admin.organization') }}</th>
                        <th scope="col">{{ text('admiro::admin.organization_parent') }}</th>
                        <th scope="col">{{ text('admiro::admin.account_state') }}</th>
                        <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($organizations as $organization)
                        <tr>
                            <th scope="row">{{ $organization->name }}</th>
                            <td>{{ $names[$organization->parent_id] ?? '—' }}</td>
                            <td><span class="ss-badge">{{ $organization->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}</span></td>
                            <td>
                                <div class="ss-context__actions">
                                    <a class="ss-button" href="{{ route('admiro.organizations.members', ['organization' => $organization->token]) }}"><x-admiro::icon name="users" class="ss-button__icon" />{{ text('admiro::admin.members') }}</a>
                                    @if ($canManage)
                                        <a class="ss-button" href="{{ route('admiro.organizations.edit', ['organization' => $organization->token]) }}">{{ text_action('edit') }}</a>
                                        @if ($organization->id !== \Simplysmart\Admiro\Models\Organization::OPERATOR_ID)
                                            <form method="POST" action="{{ route('admiro.organizations.toggle', ['organization' => $organization->token]) }}">
                                                @csrf
                                                <button class="ss-button" type="submit">{{ $organization->is_active ? text('admiro::admin.action.deactivate') : text('admiro::admin.organization_enable') }}</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admiro::zone.layout>
