{{-- Onboarding (OnboardingController::index): karta z nagłówkiem (ikona rakiety, tytuł, opis), po prawej pasek postępu (natywny <progress>, „Zrobione X z Y”) i panele niezrobionych kroków konfiguracji w stałej kolejności (Panel\Enums\OnboardingStep — tylko kroki, które konto może wykonać), każdy z dużym numerem kroku w tle i odnośnikiem do kreatora (onboarding.show); gdy wszystko zrobione — komunikat i odnośnik do pierwszego kroku. $onboarding: list<array{step: OnboardingStep, number: int, done: bool}> (Panel\Onboarding\OnboardingProgress::checklist()). --}}
@php
    $pending = array_values(array_filter($onboarding, static fn(array $item): bool => ! $item['done']));
    $doneCount = count($onboarding) - count($pending);
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.onboarding_title')" :meta="text('admiro::dashboard.onboarding_wizard_lead')">
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
    @if ($pending === [] && $onboarding !== [])
        <p class="ss-notice">{{ text('admiro::dashboard.onboarding_complete') }} <a class="ss-link" href="{{ route('admiro.onboarding.show', ['step' => $onboarding[0]['step']->value]) }}">{{ text('admiro::dashboard.onboarding_review') }}</a></p>
    @endif
</x-admiro::zone.layout>
