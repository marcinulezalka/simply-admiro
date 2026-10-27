{{-- Wiersz administratora w zadaniu „Profile i uprawnienia” kreatora onboardingu (panel/onboarding/show; także w wynikach wyszukiwania, z kotwicą #administrator-<token>): awatar, imię i nazwisko, adres e-mail (własne konto z plakietką „To Ty”) i po prawej role jako przełączniki z nazwą roli i gałką ON / OFF, zapisywane od razu (administrators.roles.toggle, data-ss-autosubmit; nadanie i odebranie bez eskalacji — ZoneRoleAssignment). Własne konto: role tylko jako plakietki, bez zmiany. Z widoku nadrzędnego: $administrator: ZoneAccount, $rolesOf: array<int|string, list<string>> (Auth\AccountKey konta → role), $availableRoles: list<string>, $self: ZoneAccount, $canManage: bool, $prefix: string (prefiks id przełączników), $zone. --}}
@php
    $token = $administrator->getRouteKey();
    $isSelf = $administrator->is($self);
    $owned = $rolesOf[\Simplysmart\Admiro\Auth\AccountKey::of($administrator)] ?? [];
@endphp
<li class="ss-onboarding-admins__row" id="administrator-{{ $token }}">
    <span class="ss-onboarding-admins__person">
        <x-admiro::avatar :name="$administrator->displayName()" :color="$administrator->avatarColor()" />
        <span class="ss-onboarding-picker__chip-text">
            <span class="ss-onboarding-picker__name">{{ $administrator->displayName() }}@if ($isSelf) <span class="ss-badge ss-badge--outline ss-badge--info">{{ text('admiro::dashboard.onboarding_admins_self') }}</span>@endif</span>
            <span class="ss-table__sub">{{ $administrator->emailAddress() }}</span>
        </span>
    </span>
    <span class="ss-onboarding-admins__roles">
        @foreach ($availableRoles as $role)
            @if ($isSelf || ! $canManage)
                @if (in_array($role, $owned, true))
                    <span class="ss-badge ss-badge--outline">{{ text('roles.admiro.' . $role) }}</span>
                @endif
            @else
                <form method="POST" action="{{ route($zone . 'administrators.roles.toggle', ['account' => $token, 'role' => $role]) }}" data-ss-autosubmit>
                    @csrf
                    <x-admiro::switch :id="$prefix . '-' . $token . '-' . $role" :label="text('roles.admiro.' . $role)" :checked="in_array($role, $owned, true)" class="ss-onboarding-admins__role" knob />
                </form>
            @endif
        @endforeach
    </span>
</li>
