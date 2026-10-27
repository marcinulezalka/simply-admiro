{{-- Onboarding (OnboardingController::index): nagłówek strony „Onboarding” (nad nim „Wstecz”), w panelu akcji zakładka postępu przy górnej krawędzi strony, z prawej (ciemny przycisk z ikoną celu `goal`, obok etykieta „Zrobione X z Y” i pod nią pasek), która rozwija listę zadań — na telefonie w panelu wysuwanym z prawej krawędzi okna, z nagłówkiem i przyciskiem zamknięcia — (numer albo haczyk, nazwa, plakietka stanu; odnośniki do kreatora), a pod separatorem przełącznik „Nie pokazuj zadań zrealizowanych” (js/hide-done.js: chowa zrobione karty [data-ss-done], wybór zapamiętany w przeglądarce; bez skryptu ukryty), pod nim — bez karty, na tle strony — lista numerowana wszystkich zadań jako paneli-odnośników do kreatora (etykieta „Zadanie N · stan” dla zrobionych i w trakcie; zrobione wyszarzone w kolorach hovera: .ss-card--muted, z haczykiem); po wszystkich zadaniach komunikat z linkiem do przeglądu. $onboarding: list<array{step: OnboardingStep, number: int, state: OnboardingStepState, done: bool}> --}}
@php
    $doneCount = count(array_filter($onboarding, static fn (array $item): bool => $item['done']));
    $total = count($onboarding);
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.onboarding_title')">
    @if ($onboarding !== [])
        <x-slot:actions class="ss-context__actions--progress">
            <details class="ss-onboarding__progress-menu" data-ss-dropdown>
                <summary class="ss-onboarding__progress">
                    <span class="ss-onboarding__progress-button" aria-hidden="true"><x-admiro::icon name="goal" class="ss-onboarding__progress-icon" /></span>
                    <span class="ss-onboarding__progress-text">
                        <span class="ss-onboarding__progress-label" id="ss-onboarding-progress-label">{{ text('admiro::dashboard.onboarding_progress', ['done' => (string) $doneCount, 'total' => (string) $total]) }}<x-admiro::icon name="chevron-down" class="ss-onboarding__progress-chevron" /></span>
                        <progress class="ss-progress" id="ss-onboarding-progress" value="{{ $doneCount }}" max="{{ $total }}" aria-labelledby="ss-onboarding-progress-label">{{ $doneCount }}/{{ $total }}</progress>
                    </span>
                </summary>
                <div class="ss-account-menu__panel ss-onboarding__progress-panel">
                    <div class="ss-onboarding__progress-head">
                        <p class="ss-onboarding__progress-title">{{ text('admiro::dashboard.onboarding_progress', ['done' => (string) $doneCount, 'total' => (string) $total]) }}</p>
                        <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-dropdown-close>
                            <x-admiro::icon name="close" class="ss-icon-button__icon" />
                            <span class="ss-sr-only">{{ text('layout.close') }}</span>
                        </button>
                    </div>
                    <ol class="ss-onboarding__progress-steps">
                        @foreach ($onboarding as ['step' => $step, 'number' => $number, 'state' => $state, 'done' => $done])
                            <li>
                                <a class="ss-account-menu__link ss-onboarding__progress-step" href="{{ route('admiro.onboarding.show', ['step' => $step->value]) }}">
                                    <span @class(['ss-onboarding__progress-marker', 'ss-onboarding__progress-marker--done' => $done]) aria-hidden="true">
                                        @if ($done)
                                            <x-admiro::icon name="check" class="ss-onboarding__progress-marker-icon" />
                                        @else
                                            {{ $number }}
                                        @endif
                                    </span>
                                    <span class="ss-onboarding__progress-step-name">{{ text('admiro::dashboard.onboarding_' . $step->value . '_title') }}</span>
                                    <span class="ss-badge ss-badge--outline ss-badge--{{ $state->tone() }}">{{ $state->label() }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                    <div class="ss-onboarding__progress-setting" data-ss-hide-done-control hidden>
                        <x-admiro::switch id="ss-onboarding-hide-done" :label="text('admiro::dashboard.onboarding_hide_done')" data-ss-hide-done="ss-onboarding-steps" data-ss-hide-done-key="ss-onboarding-hide-done" />
                    </div>
                </div>
            </details>
            <x-admiro::scripts :files="['js/hide-done.js']" />
        </x-slot:actions>
    @endif

    @if ($onboarding !== [] && $doneCount === $total)
        <p class="ss-notice">{{ text('admiro::dashboard.onboarding_complete') }} <a class="ss-link" href="{{ route('admiro.onboarding.show', ['step' => $onboarding[0]['step']->value]) }}">{{ text('admiro::dashboard.onboarding_review') }}</a></p>
    @endif

    @if ($onboarding !== [])
        <ol class="ss-onboarding__steps ss-grid ss-grid--3" id="ss-onboarding-steps" aria-label="{{ text('admiro::dashboard.onboarding_steps') }}">
            @foreach ($onboarding as ['step' => $step, 'number' => $number, 'state' => $state, 'done' => $done])
                <li class="ss-onboarding__step" value="{{ $number }}"{!! $done ? ' data-ss-done' : '' !!}>
                    <a class="ss-card ss-onboarding__link{{ $done ? ' ss-card--muted' : '' }}" href="{{ route('admiro.onboarding.show', ['step' => $step->value]) }}">
                        <span class="ss-onboarding__number" aria-hidden="true">{{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="ss-onboarding__eyebrow">{{ text('admiro::dashboard.onboarding_step', ['number' => (string) $number]) }}@if ($state !== \Simplysmart\Admiro\Panel\Enums\OnboardingStepState::NotStarted) · {{ $state->label() }}@endif</span>
                        <span class="ss-onboarding__name"><x-admiro::icon :name="$done ? 'check-circle' : $step->icon()" class="ss-onboarding__name-icon" />{{ text('admiro::dashboard.onboarding_' . $step->value . '_title') }}</span>
                        <span class="ss-onboarding__text">{{ text('admiro::dashboard.onboarding_' . $step->value . '_lead') }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    @endif
</x-admiro::zone.layout>
