{{-- Pulpit administratora. Na górze karta Onboarding: nagłówek (ikona rakiety, tytuł, opis), po prawej pasek postępu (natywny <progress>, „Zrobione X z Y”) i panele niezrobionych kroków konfiguracji w stałej kolejności (Panel\Enums\OnboardingStep: języki, ustawienia, dokumenty prawne, organizacje, właściciele stref, administratorzy — tylko kroki, które konto może wykonać), każdy z dużym numerem kroku w tle i odnośnikiem do kreatora onboardingu (onboarding.show); zrobione kroki znikają, a gdy wszystkie są zrobione, karty nie ma. Pod nią skróty do sekcji, do których konto ma uprawnienia (karty-odnośniki z ikoną), albo informacja o braku uprawnień. $onboarding: list<array{step: OnboardingStep, number: int, done: bool}> (Panel\Onboarding\OnboardingProgress::checklist()); $canViewZones, $canViewOrganizations, $canAssignOwners, $canManageAdministrators, $canViewAudit, $canViewSettings: bool (uprawnienia administratora). --}}
@php
    $pending = array_values(array_filter($onboarding, static fn(array $item): bool => ! $item['done']));
    $doneCount = count($onboarding) - count($pending);
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
    @if ($pending !== [])
        <section class="ss-card ss-onboarding" aria-labelledby="ss-onboarding-title">
            <div class="ss-onboarding__header">
                <div class="ss-onboarding__intro">
                    <x-admiro::icon name="rocket" class="ss-icon--tile ss-onboarding__icon" />
                    <div>
                        <h2 class="ss-onboarding__title" id="ss-onboarding-title">{{ text('admiro::dashboard.onboarding_title') }}</h2>
                        <p class="ss-onboarding__lead">{{ \Simplysmart\Admiro\View\BrandNames::highlight(text('admiro::dashboard.onboarding_lead')) }}</p>
                    </div>
                </div>
                <div class="ss-onboarding__progress">
                    <label class="ss-onboarding__progress-label" for="ss-onboarding-progress">{{ text('admiro::dashboard.onboarding_progress', ['done' => (string) $doneCount, 'total' => (string) count($onboarding)]) }}</label>
                    <progress class="ss-progress" id="ss-onboarding-progress" value="{{ $doneCount }}" max="{{ count($onboarding) }}">{{ $doneCount }}/{{ count($onboarding) }}</progress>
                </div>
            </div>
            <ol class="ss-onboarding__steps ss-grid ss-grid--3">
                @foreach ($pending as ['step' => $step, 'number' => $number])
                    <li class="ss-onboarding__step" value="{{ $number }}">
                        <a class="ss-card ss-card--muted ss-onboarding__link" href="{{ route('admiro.onboarding.show', ['step' => $step->value]) }}">
                            <span class="ss-onboarding__number" aria-hidden="true">{{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="ss-onboarding__eyebrow">{{ text('admiro::dashboard.onboarding_step', ['number' => (string) $number]) }}</span>
                            <span class="ss-onboarding__name"><x-admiro::icon :name="$step->icon()" class="ss-onboarding__name-icon" />{{ text('admiro::dashboard.onboarding_' . $step->value . '_title') }}</span>
                            <span class="ss-onboarding__text">{{ text('admiro::dashboard.onboarding_' . $step->value . '_lead') }}</span>
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
