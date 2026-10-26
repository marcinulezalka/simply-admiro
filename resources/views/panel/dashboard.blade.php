{{-- Status systemu (pulpit administratora): skróty do sekcji, do których konto ma uprawnienia (karty-odnośniki z ikoną), albo informacja o braku uprawnień; przegląd kroków konfiguracji jest na stronie Onboarding (panel/onboarding/index). $canViewZones, $canViewOrganizations, $canAssignOwners, $canManageAdministrators, $canViewAudit, $canViewSettings: bool (uprawnienia administratora). --}}
@php
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
