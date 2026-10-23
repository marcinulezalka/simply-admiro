{{-- Administratorzy i ich role. $administrators: LengthAwarePaginator kont admiro, $roles: array<string, list<string>> (token → role), $availableRoles: list<string>, $self: zalogowany administrator. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.administrators_title')">
    @error('role') <p role="alert">{{ $message }}</p> @enderror

    <table>
        <thead>
            <tr>
                <th scope="col">{{ text('admiro::admin.account_name') }}</th>
                <th scope="col">{{ text('admiro::admin.account_email') }}</th>
                <th scope="col">{{ text('admiro::admin.roles') }}</th>
                <th scope="col">{{ text('admiro::admin.role_assign') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($administrators as $administrator)
                @php($token = $administrator->getRouteKey())
                <tr>
                    <td>{{ $administrator->displayName() }}</td>
                    <td>{{ $administrator->emailAddress() }}</td>
                    <td>
                        @forelse ($roles[$token] ?? [] as $role)
                            <span>{{ text('roles.admiro.' . $role) }}</span>
                            @unless ($administrator->is($self))
                                <form method="POST" action="{{ route('admiro.administrators.roles.revoke', ['account' => $token, 'role' => $role]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">{{ text('admiro::admin.role_revoke') }}</button>
                                </form>
                            @endunless
                        @empty
                            —
                        @endforelse
                    </td>
                    <td>
                        @unless ($administrator->is($self))
                            <form method="POST" action="{{ route('admiro.administrators.roles.assign', ['account' => $token]) }}">
                                @csrf
                                <label for="role-{{ $token }}">{{ text('admiro::admin.role') }}</label>
                                <select id="role-{{ $token }}" name="role">
                                    @foreach (array_diff($availableRoles, $roles[$token] ?? []) as $role)
                                        <option value="{{ $role }}">{{ text('roles.admiro.' . $role) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit">{{ text('admiro::admin.role_assign') }}</button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $administrators->links() }}
</x-mainboard::zone.layout>
