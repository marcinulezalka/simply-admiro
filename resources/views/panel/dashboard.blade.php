{{-- Dashboard (DashboardController): jedna karta-odnośnik do Onboardingu (.ss-onboarding-card) — duża, nieruchoma ikona celu w tle (dekoracyjna), nagłówek z ikoną po lewej i obok małą etykietą mono „Onboarding” nad tytułem, opis (inne po zrobieniu wszystkich zadań), postęp „Zrobione X z Y” z procentem i paskiem, wezwanie do działania ze strzałką — następne niezrobione zadanie, a po wszystkich „Przejrzyj zadania”; bez zadań w uprawnieniach — informacja o braku uprawnień. $onboarding: list<array{step: OnboardingStep, number: int, state: OnboardingStepState, done: bool}> --}}
@php
    $doneCount = count(array_filter($onboarding, static fn (array $item): bool => $item['done']));
    $total = count($onboarding);
    $complete = $total > 0 && $doneCount === $total;
    $next = collect($onboarding)->firstWhere('done', false)['step'] ?? null;
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.title')">
    @if ($onboarding === [])
        <p class="ss-notice">{{ text('admiro::admin.no_sections') }}</p>
    @else
        <a @class(['ss-card', 'ss-onboarding-card', 'ss-onboarding-card--complete' => $complete]) href="{{ route('admiro.onboarding.index') }}">
            <x-admiro::icon name="goal" class="ss-onboarding-card__watermark" />
            <span class="ss-onboarding-card__head">
                <x-admiro::icon :name="$complete ? 'check-circle' : 'rocket'" class="ss-onboarding-card__icon" />
                <span class="ss-onboarding-card__heading">
                    <span class="ss-onboarding-card__eyebrow">{{ text('admiro::dashboard.onboarding_title') }}</span>
                    <span class="ss-onboarding-card__title">{{ text($complete ? 'admiro::dashboard.onboarding_card_complete_title' : 'admiro::dashboard.onboarding_card_title') }}</span>
                </span>
            </span>
            <span class="ss-onboarding-card__lead">{{ text($complete ? 'admiro::dashboard.onboarding_complete' : 'admiro::dashboard.onboarding_card_lead') }}</span>
            <span class="ss-onboarding-card__progress">
                <span class="ss-onboarding-card__progress-label">
                    <span id="ss-onboarding-card-progress-label">{{ text('admiro::dashboard.onboarding_progress', ['done' => (string) $doneCount, 'total' => (string) $total]) }}</span>
                    <span class="ss-onboarding-card__percent">{{ (int) round($doneCount / $total * 100) }}%</span>
                </span>
                <progress class="ss-progress" value="{{ $doneCount }}" max="{{ $total }}" aria-labelledby="ss-onboarding-card-progress-label">{{ $doneCount }}/{{ $total }}</progress>
            </span>
            <span class="ss-onboarding-card__action">{{ $next === null ? text('admiro::dashboard.onboarding_review') : text('admiro::dashboard.onboarding_card_next', ['name' => text('admiro::dashboard.onboarding_' . $next->value . '_title')]) }}<x-admiro::icon name="arrow-right" class="ss-onboarding-card__arrow" /></span>
        </a>
    @endif
</x-admiro::zone.layout>
