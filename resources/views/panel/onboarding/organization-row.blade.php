{{-- Wiersz aktywnej organizacji w zadaniu „Organizacje” kreatora onboardingu (panel/onboarding/show): ikona, nazwa z nazwą skróconą i NIP-em, strefy, których konta są jej członkami (plakietki z nazwą strefy, adres strefy w podpowiedzi; bez nich „Brak strefy”), właściciele (rola owner w tej organizacji: imię i nazwisko i strefa; bez nich przerywana ramka „Brak właściciela” z odnośnikiem do założenia właściciela dla tej organizacji, gdy wolno — $canAssignOwners) i przełącznik zapisywany od razu (data-ss-autosubmit; organizacja operatora zablokowana). Z widoku nadrzędnego: $organization: Organization, $assignment: array{zones: list<ZoneDefinition>, owners: list<array{zone: ZoneDefinition, account: ZoneAccount}>}, $organizationSwitch, $canManage, $canAssignOwners, $zone. --}}
@php(['locked' => $locked, 'id' => $switchId, 'details' => $details] = $organizationSwitch($organization, 'enabled-organization'))
<li class="ss-onboarding-organizations__row">
    <span class="ss-onboarding-organizations__main">
        <x-admiro::icon name="building" class="ss-icon--tile ss-onboarding-picker__flags" />
        <span class="ss-onboarding-picker__chip-text">
            <span class="ss-onboarding-picker__name">{{ $organization->name }}</span>
            @if ($details !== '')
                <span class="ss-table__sub">{{ $details }}</span>
            @endif
        </span>
    </span>
    <dl class="ss-onboarding-organizations__facts">
        <div class="ss-onboarding-organizations__fact">
            <dt>{{ text('admiro::dashboard.onboarding_organization_zones') }}</dt>
            <dd>
                @forelse ($assignment['zones'] as $memberZone)
                    <span class="ss-badge ss-badge--outline" title="/{{ $memberZone->slug }}">{{ $memberZone->name }}</span>
                @empty
                    <span class="ss-muted">{{ text('admiro::dashboard.onboarding_organization_no_zone') }}</span>
                @endforelse
            </dd>
        </div>
        <div class="ss-onboarding-organizations__fact">
            <dt>{{ text('admiro::dashboard.onboarding_organization_owner') }}</dt>
            <dd>
                @forelse ($assignment['owners'] as $owner)
                    <span class="ss-onboarding-picker__chip-text">
                        <span>{{ $owner['account']->name }}</span>
                        <span class="ss-table__sub">{{ $owner['zone']->name }}</span>
                    </span>
                @empty
                    @if ($canAssignOwners)
                        <a class="ss-onboarding-organizations__missing" href="{{ route($zone . 'owners.create', ['organization' => $organization->token]) }}">{{ text('admiro::dashboard.onboarding_organization_add_owner') }}</a>
                    @else
                        <span class="ss-onboarding-organizations__missing">{{ text('admiro::dashboard.onboarding_organization_no_owner') }}</span>
                    @endif
                @endforelse
            </dd>
        </div>
    </dl>
    @if ($canManage)
        <form method="POST" action="{{ route($zone . 'organizations.toggle', ['organization' => $organization->token]) }}" data-ss-autosubmit>
            @csrf
            <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_organization_switch', ['name' => $organization->name])" :checked="true" :disabled="$locked" :title="$locked ? text('admiro::dashboard.onboarding_organization_locked') : null" hide-label />
        </form>
    @endif
</li>
