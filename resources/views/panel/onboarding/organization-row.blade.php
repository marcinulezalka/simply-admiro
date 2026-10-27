{{-- Węzeł drzewa aktywnych organizacji w zadaniu „Organizacje” kreatora onboardingu (panel/onboarding/show), rekurencyjnie z dziećmi (Organizations\OrganizationTree): wiersz ([data-ss-tree-row]) z uchwytem przeciągania, ikoną (departament — ikona poradni), nazwą z nazwą skróconą i NIP-em, strefami, których konta są jej członkami (plakietki z nazwą strefy, adres strefy w podpowiedzi; bez nich „Brak strefy”), właścicielami (rola owner w tej organizacji: imię i nazwisko i strefa; bez nich przerywana ramka „Brak właściciela” z odnośnikiem do założenia właściciela dla tej organizacji, gdy wolno — $canAssignOwners), listą „Przenieś pod” (formularz organizations.move wysyłany od razu, data-ss-tree-move; najwyższy poziom albo inna jednostka — bez departamentów; działa bez skryptu i z klawiatury) i przełącznikiem aktywności (data-ss-autosubmit; organizacja operatora zablokowana); pod nim lista dzieci. Element drzewa [data-ss-tree-item] jest przeciągalny (js/tree-dnd.js ustawia listę „Przenieś pod” i wysyła formularz), poza operatorem i departamentami; departament nie przyjmuje zrzutu (data-ss-tree-locked). Z widoku nadrzędnego: $node: array{organization: Organization, children: list<array>}, $assignments: array<int, array{zones: list<ZoneDefinition>, owners: list<array{zone: ZoneDefinition, account: ZoneAccount}>}>, $moveTargets: Collection<int, Organization> (aktywne jednostki), $organizationSwitch, $canManage, $canAssignOwners, $zone. --}}
@php
    $organization = $node['organization'];
    $assignment = $assignments[$organization->id];
    ['locked' => $locked, 'id' => $switchId, 'details' => $details] = $organizationSwitch($organization, 'enabled-organization');
    $department = $organization->isDepartment();
    $movable = $canManage && ! $locked && ! $department;
@endphp
<li class="ss-onboarding-organizations__node" data-ss-tree-item="{{ $organization->token }}" @if ($movable) draggable="true" @endif @if ($department) data-ss-tree-locked @endif>
    <div class="ss-onboarding-organizations__row" data-ss-tree-row>
        @if ($movable)
            <x-admiro::icon name="grip" class="ss-onboarding-organizations__handle" />
        @endif
        <span class="ss-onboarding-organizations__main">
            <x-admiro::icon :name="$department ? 'sitemap' : 'building'" class="ss-icon--tile ss-onboarding-picker__flags" />
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
        @if ($movable)
            <form class="ss-onboarding-organizations__move" method="POST" action="{{ route($zone . 'organizations.move', ['organization' => $organization->token]) }}" data-ss-autosubmit data-ss-tree-move>
                @csrf
                @method('PUT')
                <label class="ss-sr-only" for="move-{{ $organization->token }}">{{ text('admiro::dashboard.onboarding_organization_move', ['name' => $organization->name]) }}</label>
                <select class="ss-input" id="move-{{ $organization->token }}" name="parent">
                    <option value="" @selected($organization->parent_id === null)>{{ text('admiro::dashboard.onboarding_organization_top') }}</option>
                    @foreach ($moveTargets as $target)
                        @continue($target->id === $organization->id)
                        <option value="{{ $target->token }}" @selected($organization->parent_id === $target->id)>{{ text('admiro::dashboard.onboarding_organization_under', ['name' => $target->name]) }}</option>
                    @endforeach
                </select>
                <button class="ss-button ss-button--sm" type="submit" data-ss-autosubmit-fallback>{{ text('admiro::dashboard.onboarding_organization_move_submit') }}</button>
            </form>
        @endif
        @if ($canManage)
            <form method="POST" action="{{ route($zone . 'organizations.toggle', ['organization' => $organization->token]) }}" data-ss-autosubmit>
                @csrf
                <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_organization_switch', ['name' => $organization->name])" :checked="true" :disabled="$locked" :title="$locked ? text('admiro::dashboard.onboarding_organization_locked') : null" hide-label />
            </form>
        @endif
    </div>
    @if ($node['children'] !== [])
        <ul class="ss-onboarding-organizations ss-onboarding-organizations--children">
            @foreach ($node['children'] as $child)
                @include('admiro::panel.onboarding.organization-row', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
