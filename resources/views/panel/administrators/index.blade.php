{{-- Administratorzy i ich role: lista z awatarem, adresem i rolami jako plakietki (odebranie roli przyciskiem plakietki), nadanie roli z listy; własne konto bez akcji; stronicowanie. $administrators: LengthAwarePaginator kont admiro, $roles: array<string, list<string>> (token → role), $availableRoles: list<string>, $self: zalogowany administrator. --}}
<x-admiro::zone.layout :title="text('admiro::admin.administrators_title')">
    @error('role') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <section class="ss-card">
        <ul class="ss-list">
            @foreach ($administrators as $administrator)
                @php
                    $token = $administrator->getRouteKey();
                    $isSelf = $administrator->is($self);
                    $missing = array_values(array_diff($availableRoles, $roles[$token] ?? []));
                @endphp
                <li class="ss-list__item">
                    <span class="ss-person">
                        <x-admiro::avatar :name="$administrator->displayName()" :color="$administrator->avatarColor()" />
                        <span class="ss-person__text">
                            <strong>{{ $administrator->displayName() }}</strong>
                            <span class="ss-muted">{{ $administrator->emailAddress() }}</span>
                            <span class="ss-context__actions">
                                @forelse ($roles[$token] ?? [] as $role)
                                    @if ($isSelf)
                                        <span class="ss-badge">{{ text('roles.admiro.' . $role) }}</span>
                                    @else
                                        <form method="POST" action="{{ route('admiro.administrators.roles.revoke', ['account' => $token, 'role' => $role]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="ss-badge" type="submit" title="{{ text('admiro::admin.role_revoke') }}: {{ text('roles.admiro.' . $role) }}">{{ text('roles.admiro.' . $role) }}<x-admiro::icon name="close" class="ss-badge__icon" /><span class="ss-sr-only"> — {{ text('admiro::admin.role_revoke') }}</span></button>
                                        </form>
                                    @endif
                                @empty
                                    <span class="ss-muted">—</span>
                                @endforelse
                            </span>
                        </span>
                    </span>
                    @if (! $isSelf && $missing !== [])
                        <form class="ss-list__side ss-form-row" method="POST" action="{{ route('admiro.administrators.roles.assign', ['account' => $token]) }}">
                            @csrf
                            <label class="ss-sr-only" for="role-{{ $token }}">{{ text('admiro::admin.role') }}</label>
                            <select class="ss-input" id="role-{{ $token }}" name="role">
                                @foreach ($missing as $role)
                                    <option value="{{ $role }}">{{ text('roles.admiro.' . $role) }}</option>
                                @endforeach
                            </select>
                            <button class="ss-button" type="submit">{{ text('admiro::admin.role_assign') }}</button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    <x-admiro::pagination :paginator="$administrators" />
</x-admiro::zone.layout>
