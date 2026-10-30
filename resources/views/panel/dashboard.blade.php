{{-- Dashboard (DashboardController): jedna karta-odnośnik do Onboardingu (x-admiro::checklist.card) — ikona rakiety, etykieta „Onboarding” nad tytułem, opis (inne po zrobieniu wszystkich zadań), postęp „Zrobione X z Y” z procentem i paskiem, wezwanie do działania ze strzałką — następne niezrobione zadanie, a po wszystkich „Przejrzyj zadania”; bez zadań w uprawnieniach — informacja o braku uprawnień. $onboarding: list<array{step: OnboardingStep, number: int, state: OnboardingStepState, done: bool}> --}}
@php
    $doneCount = count(array_filter($onboarding, static fn (array $item): bool => $item['done']));
    $total = count($onboarding);
    $next = collect($onboarding)->firstWhere('done', false)['step'] ?? null;
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.title')">
    @if ($onboarding === [])
        <p class="ss-notice">{{ text('admiro::admin.no_sections') }}</p>
    @else
        <x-admiro::checklist.card
            :href="route('admiro.onboarding.index')"
            :eyebrow="text('admiro::dashboard.onboarding_title')"
            :title="text('admiro::dashboard.onboarding_card_title')"
            :complete-title="text('admiro::dashboard.onboarding_card_complete_title')"
            :lead="text('admiro::dashboard.onboarding_card_lead')"
            :complete-lead="text('admiro::dashboard.onboarding_complete')"
            :progress-label="text('admiro::dashboard.onboarding_progress', ['done' => ':done', 'total' => ':total'])"
            :next-label="text('admiro::dashboard.onboarding_card_next', ['name' => ':name'])"
            :review-label="text('admiro::dashboard.onboarding_review')"
            :done="$doneCount"
            :total="$total"
            :next="$next === null ? null : text('admiro::dashboard.onboarding_' . $next->value . '_title')"
        />
    @endif
</x-admiro::zone.layout>
