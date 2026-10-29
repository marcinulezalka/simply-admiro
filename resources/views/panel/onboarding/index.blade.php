{{-- Onboarding (OnboardingController::index) na wspólnych komponentach listy zadań Core (x-admiro::checklist.progress, x-admiro::checklist.steps, pozycje View\Checklist\ChecklistItem): nagłówek strony „Onboarding” (nad nim „Wstecz”), w panelu akcji zakładka postępu przy górnej krawędzi strony, z prawej (ciemny przycisk z ikoną celu `goal`, obok etykieta „Zrobione X z Y” i pod nią pasek), która rozwija listę zadań — na telefonie w panelu wysuwanym z prawej krawędzi okna, z nagłówkiem i przyciskiem zamknięcia — (numer albo haczyk, nazwa, plakietka stanu; odnośniki do kreatora), a pod separatorem przełącznik „Nie pokazuj zadań zrealizowanych” (js/hide-done.js: chowa zrobione karty [data-ss-done], wybór zapamiętany w przeglądarce; bez skryptu ukryty), pod nim — bez karty, na tle strony — lista numerowana wszystkich zadań jako paneli-odnośników do kreatora (etykieta „Zadanie N · stan” dla zrobionych i w trakcie; zrobione wyszarzone w kolorach hovera: .ss-card--muted, z haczykiem); po wszystkich zadaniach komunikat z linkiem do przeglądu. $onboarding: list<array{step: OnboardingStep, number: int, state: OnboardingStepState, done: bool}> --}}
@php
    $doneCount = count(array_filter($onboarding, static fn (array $item): bool => $item['done']));
    $total = count($onboarding);
    $items = array_map(static fn (array $item): \Simplysmart\Admiro\View\Checklist\ChecklistItem => new \Simplysmart\Admiro\View\Checklist\ChecklistItem(
        $item['number'],
        text('admiro::dashboard.onboarding_' . $item['step']->value . '_title'),
        text('admiro::dashboard.onboarding_' . $item['step']->value . '_lead'),
        $item['step']->icon(),
        route('admiro.onboarding.show', ['step' => $item['step']->value]),
        $item['done'],
        text('admiro::dashboard.onboarding_step', ['number' => (string) $item['number']]) . ($item['state'] !== \Simplysmart\Admiro\Panel\Enums\OnboardingStepState::NotStarted ? ' · ' . $item['state']->label() : ''),
        $item['state']->label(),
        $item['state']->tone(),
    ), $onboarding);
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.onboarding_title')">
    @if ($onboarding !== [])
        <x-slot:actions class="ss-context__actions--progress">
            <x-admiro::checklist.progress :items="$items" :label="text('admiro::dashboard.onboarding_progress', ['done' => (string) $doneCount, 'total' => (string) $total])" :hide-done-label="text('admiro::dashboard.onboarding_hide_done')" list-id="ss-onboarding-steps" storage-key="ss-onboarding-hide-done" />
        </x-slot:actions>
    @endif

    @if ($onboarding !== [] && $doneCount === $total)
        <p class="ss-notice">{{ text('admiro::dashboard.onboarding_complete') }} <a class="ss-link" href="{{ route('admiro.onboarding.show', ['step' => $onboarding[0]['step']->value]) }}">{{ text('admiro::dashboard.onboarding_review') }}</a></p>
    @endif

    @if ($onboarding !== [])
        <x-admiro::checklist.steps :items="$items" id="ss-onboarding-steps" :label="text('admiro::dashboard.onboarding_steps')" />
    @endif
</x-admiro::zone.layout>
