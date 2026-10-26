{{-- Kreator onboardingu (OnboardingController::show): nagłówek „Onboarding”, kroki jako stepper z odnośnikami (zrobione z haczykiem, bieżący z aria-current), treść bieżącego kroku i nawigacja Wstecz/Dalej. Krok „Języki”: panel włączonych języków nad listą (flaga, nazwa, nazwa własna, przełącznik), lista wszystkich języków albo pasujących do `q` z globalnej wyszukiwarki (kod z flagami krajów języka, nazwa, przełącznik; wiersz z kotwicą #language-<kod>), przełączniki wysyłają się od razu (data-ss-autosubmit; język aplikacji i interfejsu zablokowany), „Zatwierdź i dalej” zamyka krok. Pozostałe kroki: opis i przejście do strony panelu. $checklist: list<array{step: OnboardingStep, number: int, done: bool}>; $current, ?$previous, ?$next: OnboardingStep; dla języków $term: string, $enabled i $languages: Collection<int, Language>, $canManage: bool. --}}
@php
    $zone = 'admiro.';
    $stepTitle = static fn (\Simplysmart\Admiro\Panel\Enums\OnboardingStep $step): string => text('admiro::dashboard.onboarding_' . $step->value . '_title');
    $currentDone = collect($checklist)->firstWhere('step', $current)['done'] ?? false;
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.onboarding_title')" :meta="text('admiro::dashboard.onboarding_wizard_lead')">
    <nav aria-label="{{ text('admiro::dashboard.onboarding_steps') }}">
        <ol class="ss-stepper ss-onboarding-wizard__steps">
            @foreach ($checklist as ['step' => $step, 'number' => $number, 'done' => $done])
                @php($state = $step === $current ? 'current' : ($done ? 'done' : 'next'))
                <li class="ss-stepper__step ss-stepper__step--{{ $state }}">
                    <a class="ss-onboarding-wizard__step-link" href="{{ route($zone . 'onboarding.show', ['step' => $step->value]) }}" @if ($step === $current) aria-current="step" @endif>
                        <span class="ss-stepper__marker" aria-hidden="true">
                            @if ($done)
                                <x-admiro::icon name="check" class="ss-stepper__icon" />
                            @else
                                {{ $number }}
                            @endif
                        </span>
                        <span class="ss-stepper__label">{{ $stepTitle($step) }}@if ($done)<span class="ss-sr-only"> ({{ text('admiro::dashboard.onboarding_done') }})</span>@endif</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>

    <section class="ss-card ss-onboarding-wizard" aria-labelledby="ss-onboarding-step-title">
        <div class="ss-onboarding-wizard__header">
            <x-admiro::icon :name="$current->icon()" class="ss-icon--tile" />
            <div>
                <p class="ss-onboarding__eyebrow">{{ text('admiro::dashboard.onboarding_step', ['number' => (string) (collect($checklist)->firstWhere('step', $current)['number'] ?? 1)]) }}@if ($currentDone) · {{ text('admiro::dashboard.onboarding_done') }}@endif</p>
                <h2 class="ss-onboarding-wizard__title" id="ss-onboarding-step-title">{{ $stepTitle($current) }}</h2>
                <p class="ss-muted ss-onboarding-wizard__lead">{{ text('admiro::dashboard.onboarding_' . $current->value . '_lead') }}</p>
            </div>
        </div>

        @if ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages)
            @php($languageSwitch = static fn (\Simplysmart\Admiro\Models\Dictionaries\Language $language, string $prefix): array => [
                'locked' => $language->is_default || $language->is_interface,
                'id' => $prefix . '-' . $language->code,
            ])

            <section class="ss-card ss-card--muted ss-onboarding-languages__enabled" aria-labelledby="ss-languages-enabled-title">
                <h3 class="ss-card__title" id="ss-languages-enabled-title">{{ text('admiro::dashboard.onboarding_languages_enabled') }} <span class="ss-badge">{{ $enabled->count() }}</span></h3>
                @if ($enabled->isEmpty())
                    <p class="ss-muted">{{ text('admiro::dashboard.onboarding_languages_enabled_empty') }}</p>
                @else
                    <ul class="ss-onboarding-languages__chips">
                        @foreach ($enabled as $language)
                            @php(['locked' => $locked, 'id' => $switchId] = $languageSwitch($language, 'enabled-language'))
                            <li class="ss-onboarding-languages__chip">
                                <span class="ss-onboarding-languages__flags" aria-hidden="true">
                                    @foreach (array_slice($language->flagIcons(), 0, 1) as $flag)
                                        <x-admiro::flag :icon="$flag" />
                                    @endforeach
                                </span>
                                <span class="ss-onboarding-languages__chip-text">
                                    <span class="ss-onboarding-languages__name">{{ $language->translatedName() }}</span>
                                    <span class="ss-table__sub">{{ $language->native_name }} · {{ $language->code }}</span>
                                </span>
                                @if ($canManage)
                                    <form method="POST" action="{{ route($zone . 'dictionaries.toggle', ['dictionary' => 'languages', 'code' => $language->code]) }}" data-ss-autosubmit>
                                        @csrf
                                        <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_language_switch', ['name' => $language->translatedName()])" :checked="true" :disabled="$locked" :title="$locked ? text('admiro::dashboard.onboarding_language_locked') : null" hide-label />
                                        @unless ($locked)
                                            <button class="ss-button ss-button--sm" type="submit" data-ss-autosubmit-fallback>{{ text('admiro::dashboard.onboarding_language_save') }}</button>
                                        @endunless
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="ss-onboarding-languages__list" aria-labelledby="ss-languages-all-title">
                <div class="ss-onboarding-languages__list-header">
                    <h3 class="ss-card__title" id="ss-languages-all-title">{{ $term === '' ? text('admiro::dashboard.onboarding_languages_all') : text('admiro::dashboard.onboarding_languages_results', ['term' => $term]) }}</h3>
                    @if ($term !== '')
                        <a class="ss-link" href="{{ route($zone . 'onboarding.show', ['step' => $current->value]) }}">{{ text('admiro::dashboard.onboarding_languages_clear') }}</a>
                    @else
                        <p class="ss-muted ss-onboarding-languages__hint">{{ text('admiro::dashboard.onboarding_languages_hint') }}</p>
                    @endif
                </div>

                @if ($languages->isEmpty())
                    <p class="ss-notice">{{ text('admiro::dashboard.onboarding_languages_none', ['term' => $term]) }}</p>
                @else
                    <div class="ss-card ss-card--flush">
                        <div class="ss-table-wrap">
                            <table class="ss-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ text('admiro::admin.dictionary_code') }}</th>
                                        <th scope="col">{{ text('admiro::admin.dictionary_name') }}</th>
                                        <th scope="col">{{ text('admiro::admin.account_state') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($languages as $language)
                                        @php(['locked' => $locked, 'id' => $switchId] = $languageSwitch($language, 'language'))
                                        <tr class="ss-onboarding-languages__row" id="language-{{ $language->code }}">
                                            <td class="ss-table__number">
                                                <span class="ss-dictionary-code">
                                                    <span class="ss-dictionary-code__flags" aria-hidden="true">
                                                        @foreach (array_slice($language->flagIcons(), 0, 4) as $flag)
                                                            <x-admiro::flag :icon="$flag" />
                                                        @endforeach
                                                        @if (count($language->flagIcons()) > 4)
                                                            <span class="ss-dictionary-code__more">+{{ count($language->flagIcons()) - 4 }}</span>
                                                        @endif
                                                    </span>
                                                    {{ $language->code }}
                                                </span>
                                            </td>
                                            <th scope="row">
                                                {{ $language->translatedName() }}
                                                @if (filled($language->native_name) && $language->native_name !== $language->translatedName())
                                                    <span class="ss-table__sub">{{ $language->native_name }}</span>
                                                @endif
                                            </th>
                                            <td>
                                                @if ($canManage)
                                                    <form method="POST" action="{{ route($zone . 'dictionaries.toggle', ['dictionary' => 'languages', 'code' => $language->code]) }}" data-ss-autosubmit>
                                                        @csrf
                                                        <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_language_switch', ['name' => $language->translatedName()])" :checked="$language->is_active" :disabled="$locked" :title="$locked ? text('admiro::dashboard.onboarding_language_locked') : null" hide-label />
                                                        @unless ($locked)
                                                            <button class="ss-button ss-button--sm" type="submit" data-ss-autosubmit-fallback>{{ text('admiro::dashboard.onboarding_language_save') }}</button>
                                                        @endunless
                                                    </form>
                                                @else
                                                    <span class="ss-badge">{{ $language->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </section>
        @else
            <p>
                <a class="ss-button" href="{{ route($zone . $current->route(), $current->routeParameters()) }}"><x-admiro::icon :name="$current->icon()" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_open', ['name' => $stepTitle($current)]) }}</a>
            </p>
        @endif

        <div class="ss-onboarding-wizard__footer">
            @if ($previous !== null)
                <a class="ss-button" href="{{ route($zone . 'onboarding.show', ['step' => $previous->value]) }}"><x-admiro::icon name="arrow-left" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_back') }}</a>
            @else
                <span></span>
            @endif

            @if ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages && $canManage)
                <form method="POST" action="{{ route($zone . 'onboarding.confirm', ['step' => $current->value]) }}">
                    @csrf
                    <button class="ss-button ss-button--primary" type="submit">{{ $next === null ? text('admiro::dashboard.onboarding_confirm_last') : text('admiro::dashboard.onboarding_confirm') }}<x-admiro::icon name="arrow-right" class="ss-button__icon" /></button>
                </form>
            @elseif ($next !== null)
                <a class="ss-button ss-button--primary" href="{{ route($zone . 'onboarding.show', ['step' => $next->value]) }}">{{ text('admiro::dashboard.onboarding_next') }}<x-admiro::icon name="arrow-right" class="ss-button__icon" /></a>
            @endif
        </div>
    </section>
</x-admiro::zone.layout>
