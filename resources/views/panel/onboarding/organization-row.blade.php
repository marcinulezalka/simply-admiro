{{-- Węzeł drzewa organizacji w zadaniu „Organizacje” kreatora onboardingu (panel/onboarding/show), rekurencyjnie z dziećmi (Organizations\OrganizationTree) połączonymi cienkimi liniami: wiersz ([data-ss-tree-row]) z uchwytem przeciągania, ikoną (departament — ikona poradni), nazwą z nazwą skróconą i NIP-em, plakietkami paczek (Organizations\OrganizationBadges, np. kooperacja) i — gdy organizacja nie jest aktywna — plakietką jej stanu (RecordStatus, np. Zablokowany: wyłączona czasowo, dane zostają; wyłączona przed wprowadzeniem stanów — „Nieaktywna”; wiersz wyszarzony), strefami, których konta są jej członkami (favicona strefy bez nazwy — View\ZoneFavicons; bez favicony ikona i nazwa —, odnośnik do kont strefy `zones.accounts` przy `zones.view`; nazwa i adres strefy w podpowiedzi; bez nich „Brak strefy”), właścicielami jako awatary (rola owner w tej organizacji; imię i nazwisko w podpowiedzi; przy `zones.view` odnośnik do konta na liście kont strefy — `zones.accounts` z jego adresem e-mail; bez nich przerywana ramka „Brak właściciela” z odnośnikiem do założenia właściciela dla tej organizacji, gdy wolno — $canAssignOwners) i przełącznikiem włączenia (data-ss-autosubmit; organizacja operatora — domyślna i wymagana — ma w miejscu przełącznika wypełnioną ikonę ticket-slash, x-admiro::default-badge); pod nim lista dzieci. Element drzewa [data-ss-tree-item] jest przeciągalny (js/tree-dnd.js ustawia ukryte pole `parent` formularza organizations.move i go wysyła; organizacja nadrzędna widoczna jest jako rodzic w drzewie), poza operatorem i departamentami; departament i organizacja nieaktywna nie przyjmują zrzutu (data-ss-tree-locked). Linie drzewa (CSS .ss-onboarding-organizations__tree): z dolnej krawędzi panelu rodzica (pod ikoną) łukiem do lewej krawędzi panelu każdego dziecka, z kropkami na styku; uchwyt przeciągania po lewej w pionowym środku wiersza; przełącznik z podpisem ON / OFF. Z widoku nadrzędnego: $node: array{organization: Organization, children: list<array>}, $assignments: array<int, array{zones: list<ZoneDefinition>, owners: list<array{zone: ZoneDefinition, account: ZoneAccount}>}>, $badges: array<int, list<array{label: string, tone: string}>>, $organizationSwitch, $canManage, $canAssignOwners, $canViewZones, $zone. --}}
@php
    $organization = $node['organization'];
    $assignment = $assignments[$organization->id];
    ['locked' => $locked, 'id' => $switchId, 'details' => $details] = $organizationSwitch($organization, 'enabled-organization');
    $department = $organization->isDepartment();
    $active = $organization->is_active === true;
    $status = $organization->status instanceof \Simplysmart\Admiro\Enums\RecordStatus && $organization->status !== \Simplysmart\Admiro\Enums\RecordStatus::Active ? $organization->status : null;
    $movable = $canManage && ! $locked && ! $department;
@endphp
<li class="ss-onboarding-organizations__node" data-ss-tree-item="{{ $organization->token }}" @if ($movable) draggable="true" @endif @if ($department || ! $active) data-ss-tree-locked @endif>
    <div @class(['ss-onboarding-organizations__row', 'ss-onboarding-organizations__row--inactive' => ! $active, 'ss-onboarding-organizations__row--parent' => $node['children'] !== []]) data-ss-tree-row>
        @if ($movable)
            <x-admiro::icon name="grip" class="ss-onboarding-organizations__handle" />
            <form method="POST" action="{{ route($zone . 'organizations.move', ['organization' => $organization->token]) }}" hidden data-ss-tree-move>
                @csrf
                @method('PUT')
                <input type="hidden" name="parent" value="">
            </form>
        @endif
        <span class="ss-onboarding-organizations__main">
            <x-admiro::icon :name="$department ? 'sitemap' : 'building'" class="ss-icon--tile ss-onboarding-picker__flags" />
            <span class="ss-onboarding-picker__chip-text">
                <span class="ss-onboarding-picker__name">{{ $organization->name }}</span>
                @if ($details !== '')
                    <span class="ss-table__sub">{{ $details }}</span>
                @endif
                @if (! $active || ($badges[$organization->id] ?? []) !== [])
                    <span class="ss-onboarding-organizations__badges">
                        @if (! $active)
                            <span class="ss-badge ss-badge--outline ss-badge--{{ $status?->tone() ?? 'muted' }}">{{ $status?->label() ?? text('admiro::admin.state_inactive') }}</span>
                        @endif
                        @foreach ($badges[$organization->id] ?? [] as $badge)
                            <span class="ss-badge ss-badge--outline ss-badge--{{ $badge['tone'] }}">{{ $badge['label'] }}</span>
                        @endforeach
                    </span>
                @endif
            </span>
        </span>
        <dl class="ss-onboarding-organizations__facts">
            <div class="ss-onboarding-organizations__fact">
                <dt>{{ text('admiro::dashboard.onboarding_organization_zones') }}</dt>
                <dd>
                    @forelse ($assignment['zones'] as $memberZone)
                        @php($zoneFavicon = \Simplysmart\Admiro\View\ZoneFavicons::url($memberZone))
                        <{{ $canViewZones ? 'a' : 'span' }} class="ss-onboarding-organizations__zone" @if ($canViewZones) href="{{ route($zone . 'zones.accounts', ['managedZone' => $memberZone->name]) }}" @endif title="{{ $memberZone->name }} · /{{ $memberZone->slug }}">
                            @if ($zoneFavicon !== null)
                                <img class="ss-onboarding-organizations__zone-favicon" src="{{ $zoneFavicon }}" alt="{{ $memberZone->name }}" width="20" height="20">
                            @else
                                <x-admiro::icon name="zones" class="ss-onboarding-organizations__zone-icon" />{{ $memberZone->name }}
                            @endif
                        </{{ $canViewZones ? 'a' : 'span' }}>
                    @empty
                        <span class="ss-muted">{{ text('admiro::dashboard.onboarding_organization_no_zone') }}</span>
                    @endforelse
                </dd>
            </div>
            <div class="ss-onboarding-organizations__fact">
                <dt>{{ text('admiro::dashboard.onboarding_organization_owner') }}</dt>
                <dd>
                    @if ($assignment['owners'] !== [])
                        <span class="ss-avatars">
                            @foreach ($assignment['owners'] as $owner)
                                @if ($canViewZones)
                                    <a class="ss-onboarding-organizations__owner" href="{{ route($zone . 'zones.accounts', ['managedZone' => $owner['zone']->name, 'email' => $owner['account']->email]) }}" title="{{ $owner['account']->name }}"><x-admiro::avatar :name="$owner['account']->name" :color="$owner['account']->avatarColor()" size="sm" label /></a>
                                @else
                                    <x-admiro::avatar :name="$owner['account']->name" :color="$owner['account']->avatarColor()" size="sm" label :title="$owner['account']->name" />
                                @endif
                            @endforeach
                        </span>
                    @elseif ($canAssignOwners)
                        <a class="ss-onboarding-organizations__missing" href="{{ route($zone . 'owners.create', ['organization' => $organization->token]) }}">{{ text('admiro::dashboard.onboarding_organization_add_owner') }}</a>
                    @else
                        <span class="ss-onboarding-organizations__missing">{{ text('admiro::dashboard.onboarding_organization_no_owner') }}</span>
                    @endif
                </dd>
            </div>
        </dl>
        @if ($locked)
            <x-admiro::default-badge :label="text('admiro::dashboard.onboarding_organization_default')" :title="text('admiro::dashboard.onboarding_organization_locked')" />
        @elseif ($canManage)
            <form method="POST" action="{{ route($zone . 'organizations.toggle', ['organization' => $organization->token]) }}" data-ss-autosubmit>
                @csrf
                <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_organization_switch', ['name' => $organization->name])" :checked="$active" hide-label knob />
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
