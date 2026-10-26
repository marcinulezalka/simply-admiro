{{-- Onboarding (OnboardingController::index): nagłówek strony „Onboarding” z opisem, po prawej w panelu akcji karta postępu (przerywana ramka, etykieta „Zrobione X z Y” i pasek), pod nim — bez karty, na tle strony — lista numerowana wszystkich kroków jako paneli-odnośników do kreatora (zrobione przygaszone: .ss-card--dashed, z haczykiem); po wszystkich krokach komunikat z linkiem do przeglądu. $onboarding: list<array{step: OnboardingStep, number: int, done: bool}> --}}
@php
    $doneCount = count(array_filter($onboarding, static fn (array $item): bool => $item['done']));
    $total = count($onboarding);
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.onboarding_title')" :meta="text('admiro::dashboard.onboarding_lead')">
    @if ($onboarding !== [])
        <x-slot:actions class="ss-context__actions--progress">
            <div class="ss-onboarding__progress">
                <label class="ss-onboarding__progress-label" for="ss-onboarding-progress">{{ text('admiro::dashboard.onboarding_progress', ['done' => (string) $doneCount, 'total' => (string) $total]) }}</label>
                <progress class="ss-progress" id="ss-onboarding-progress" value="{{ $doneCount }}" max="{{ $total }}">{{ $doneCount }}/{{ $total }}</progress>
            </div>
        </x-slot:actions>
    @endif

    @if ($onboarding !== [] && $doneCount === $total)
        <p class="ss-notice">{{ text('admiro::dashboard.onboarding_complete') }} <a class="ss-link" href="{{ route('admiro.onboarding.show', ['step' => $onboarding[0]['step']->value]) }}">{{ text('admiro::dashboard.onboarding_review') }}</a></p>
    @endif

    @if ($onboarding !== [])
        <ol class="ss-onboarding__steps ss-grid ss-grid--3" aria-label="{{ text('admiro::dashboard.onboarding_steps') }}">
            @foreach ($onboarding as ['step' => $step, 'number' => $number, 'done' => $done])
                <li class="ss-onboarding__step" value="{{ $number }}">
                    <a class="ss-card ss-onboarding__link{{ $done ? ' ss-card--dashed' : '' }}" href="{{ route('admiro.onboarding.show', ['step' => $step->value]) }}">
                        <span class="ss-onboarding__number" aria-hidden="true">{{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="ss-onboarding__eyebrow">{{ text('admiro::dashboard.onboarding_step', ['number' => (string) $number]) }}@if ($done) · {{ text('admiro::dashboard.onboarding_done') }}@endif</span>
                        <span class="ss-onboarding__name"><x-admiro::icon :name="$done ? 'check-circle' : $step->icon()" class="ss-onboarding__name-icon" />{{ text('admiro::dashboard.onboarding_' . $step->value . '_title') }}</span>
                        <span class="ss-onboarding__text">{{ text('admiro::dashboard.onboarding_' . $step->value . '_lead') }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    @endif
</x-admiro::zone.layout>
