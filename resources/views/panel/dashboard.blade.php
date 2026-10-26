{{-- Pulpit administratora. Na górze sekcja Onboarding (ikona rakiety, opis i panele kroków konfiguracji systemu w stałej kolejności: słowniki, ustawienia, dokumenty prawne, organizacje, właściciele stref, administratorzy — tylko kroki, które konto może wykonać, numerowane po kolei, z dużym numerem w tle), pod nią skróty do sekcji, do których konto ma uprawnienia (karty-odnośniki z ikoną), albo informacja o braku uprawnień. $canViewZones, $canViewOrganizations, $canAssignOwners, $canManageAdministrators, $canViewAudit, $canViewSettings, $canManageSettings, $canManageOrganizations: bool (uprawnienia administratora). --}}
@php
    $steps = array_values(array_filter([
        $canManageSettings ? ['admiro.dictionaries.index', ['dictionary' => 'countries'], 'dictionaries', 'dictionaries'] : null,
        $canManageSettings ? ['admiro.settings.show', [], 'settings', 'locale'] : null,
        $canManageSettings ? ['admiro.cookie-policy.index', [], 'legal', 'cookie'] : null,
        $canManageOrganizations ? ['admiro.organizations.create', [], 'organizations', 'building'] : null,
        $canAssignOwners ? ['admiro.owners.create', [], 'owners', 'user-plus'] : null,
        $canManageAdministrators ? ['admiro.administrators.index', [], 'administrators', 'shield-check'] : null,
    ]));
    $shortcuts = array_filter([
        $canViewZones ? ['admiro.zones.index', 'admiro::admin.zones_title', 'zones'] : null,
        $canViewOrganizations ? ['admiro.organizations.index', 'admiro::admin.organizations_title', 'building'] : null,
        $canAssignOwners ? ['admiro.owners.create', 'admiro::admin.owner_title', 'user-plus'] : null,
        $canManageAdministrators ? ['admiro.administrators.index', 'admiro::admin.administrators_title', 'shield-check'] : null,
        $canViewAudit ? ['admiro.audit.index', 'admiro::admin.audit_title', 'history'] : null,
        $canViewSettings ? ['admiro.settings.show', 'admiro::admin.settings_title', 'settings'] : null,
    ]);
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.title')">
    @if ($steps !== [])
        <section class="ss-onboarding" aria-labelledby="ss-onboarding-title">
            <div class="ss-onboarding__header">
                <x-admiro::icon name="rocket" class="ss-icon--tile ss-onboarding__icon" />
                <div>
                    <h2 class="ss-onboarding__title" id="ss-onboarding-title">{{ text('admiro::dashboard.onboarding_title') }}</h2>
                    <p class="ss-onboarding__lead">{{ \Simplysmart\Admiro\View\BrandNames::highlight(text('admiro::dashboard.onboarding_lead')) }}</p>
                </div>
            </div>
            <ol class="ss-onboarding__steps ss-grid ss-grid--3">
                @foreach ($steps as $index => [$route, $parameters, $key, $icon])
                    <li class="ss-onboarding__step">
                        <a class="ss-card ss-onboarding__link" href="{{ route($route, $parameters) }}">
                            <span class="ss-onboarding__number" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="ss-onboarding__eyebrow">{{ text('admiro::dashboard.onboarding_step', ['number' => (string) ($index + 1)]) }}</span>
                            <span class="ss-onboarding__name"><x-admiro::icon :name="$icon" class="ss-onboarding__name-icon" />{{ text('admiro::dashboard.onboarding_' . $key . '_title') }}</span>
                            <span class="ss-onboarding__text">{{ text('admiro::dashboard.onboarding_' . $key . '_lead') }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    @if ($shortcuts === [])
        <p class="ss-notice">{{ text('admiro::admin.no_sections') }}</p>
    @else
        <nav class="ss-dashboard__shortcuts" aria-labelledby="ss-shortcuts-title">
            <h2 class="ss-dashboard__title" id="ss-shortcuts-title">{{ text('admiro::dashboard.shortcuts_title') }}</h2>
            <div class="ss-grid ss-grid--3">
                @foreach ($shortcuts as [$route, $label, $icon])
                    <a class="ss-card ss-card--link" href="{{ route($route) }}">
                        <x-admiro::icon :name="$icon" class="ss-icon--tile" />
                        <span>{{ text($label) }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
    @endif
</x-admiro::zone.layout>
