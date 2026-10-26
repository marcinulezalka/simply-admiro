{{-- Kreator onboardingu (OnboardingController::show): nagłówek „Onboarding”, pod nim krojem mono „Krok N · stan” bieżącego kroku (zrobione, w trakcie, nierozpoczęte — OnboardingStepState), obok treści pionowa lista kroków jako karty-odnośniki (numer — dla zrobionych haczyk —, nazwa, opis — tylko niezrobionych — i ikona; bieżący z aria-current="step"), karta bieżącego kroku z treścią i stopką: po lewej Wstecz i plakietka stanu kroku (obrys w kolorze stanu, OnboardingStepState::tone()), po prawej „Krok N →” do następnego. Krok „Instalacja systemu i konto hero”: instrukcja etapami z poleceniami (Panel\Onboarding\InstallationGuide, $installationGuide: list<array{key: string, commands: list<string>}>). Krok „Języki”: panel włączonych języków nad listą (flaga, nazwa, nazwa własna, przełącznik), pole wyszukiwania języka (także po kraju) z podpowiedziami (search.suggest w zakresie języków, js/global-search.js) i — dopiero po wyszukaniu `q` — wyniki w panelach (flagi krajów języka, nazwa, nazwa własna i kod, przełącznik; panel z kotwicą #language-<kod>), przełączniki wysyłają się od razu (data-ss-autosubmit; język aplikacji i interfejsu zablokowany), „Zatwierdź i dalej” zamyka krok. Pozostałe kroki: opis i przejście do strony panelu. $checklist: list<array{step: OnboardingStep, number: int, state: OnboardingStepState, done: bool}>; $current, ?$previous, ?$next: OnboardingStep; dla języków $term: string, $enabled i $languages: Collection<int, Language>, $canManage: bool. --}}
@php
    $zone = 'admiro.';
    $stepTitle = static fn (\Simplysmart\Admiro\Panel\Enums\OnboardingStep $step): string => text('admiro::dashboard.onboarding_' . $step->value . '_title');
    $currentItem = collect($checklist)->firstWhere('step', $current);
    $currentState = $currentItem['state'] ?? \Simplysmart\Admiro\Panel\Enums\OnboardingStepState::NotStarted;
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.onboarding_title')" :meta="text('admiro::dashboard.onboarding_step', ['number' => (string) ($currentItem['number'] ?? 1)]) . ' · ' . $currentState->label()">
    <div class="ss-onboarding-wizard">
        <nav class="ss-onboarding-wizard__nav" aria-label="{{ text('admiro::dashboard.onboarding_steps') }}">
            <ol class="ss-onboarding-wizard__steps">
                @foreach ($checklist as ['step' => $step, 'number' => $number, 'state' => $state, 'done' => $done])
                    <li class="ss-onboarding-wizard__step">
                        <a class="ss-onboarding-wizard__step-link{{ $done ? ' ss-onboarding-wizard__step-link--done' : '' }}" href="{{ route($zone . 'onboarding.show', ['step' => $step->value]) }}"{!! $step === $current ? ' aria-current="step"' : '' !!}>
                            <span class="ss-onboarding-wizard__marker" aria-hidden="true">
                                @if ($done)
                                    <x-admiro::icon name="check" class="ss-onboarding-wizard__marker-icon" />
                                @else
                                    {{ $number }}
                                @endif
                            </span>
                            <span class="ss-onboarding-wizard__step-text">
                                <span class="ss-onboarding-wizard__step-title">{{ $stepTitle($step) }}<span class="ss-sr-only"> ({{ $state->label() }})</span></span>
                                @unless ($done)
                                    <span class="ss-onboarding-wizard__step-lead">{{ text('admiro::dashboard.onboarding_' . $step->value . '_lead') }}</span>
                                @endunless
                            </span>
                            <x-admiro::icon :name="$step->icon()" class="ss-icon--tile ss-onboarding-wizard__step-icon" />
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>

        <section class="ss-card ss-onboarding-wizard__panel" aria-labelledby="ss-onboarding-step-title">
            <div class="ss-onboarding-wizard__header">
                <x-admiro::icon :name="$current->icon()" class="ss-icon--tile" />
                <div>
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
                    <h3 class="ss-card__title" id="ss-languages-all-title">{{ $term === '' ? text('admiro::dashboard.onboarding_languages_search_title') : text('admiro::dashboard.onboarding_languages_results', ['term' => $term]) }}</h3>
                    <form class="ss-search ss-onboarding-languages__search" role="search" method="GET" action="{{ route($zone . 'onboarding.show', ['step' => $current->value]) }}" data-ss-search-suggest="{{ route($zone . 'search.suggest') }}">
                        <input type="hidden" name="in[]" value="{{ \Simplysmart\Admiro\Panel\Enums\SearchScope::Languages->value }}">
                        <label class="ss-sr-only" for="ss-languages-search">{{ text('admiro::dashboard.onboarding_languages_search_label') }}</label>
                        <x-admiro::icon name="search" class="ss-search__icon" />
                        <input class="ss-search__input" id="ss-languages-search" name="q" type="search" value="{{ $term }}" placeholder="{{ text('admiro::dashboard.onboarding_languages_search_placeholder') }}" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ss-languages-suggestions" data-ss-search>
                        <div class="ss-search__suggestions" id="ss-languages-suggestions" role="listbox" aria-label="{{ text('layout.search_suggestions') }}" hidden data-ss-search-suggestions></div>
                    </form>

                    @if ($term === '')
                        <p class="ss-muted ss-onboarding-languages__hint">{{ text('admiro::dashboard.onboarding_languages_hint') }}</p>
                    @elseif ($languages->isEmpty())
                        <p class="ss-notice">{{ text('admiro::dashboard.onboarding_languages_none', ['term' => $term]) }}</p>
                    @else
                        <ul class="ss-grid ss-grid--3 ss-onboarding-languages__results">
                            @foreach ($languages as $language)
                                @php(['locked' => $locked, 'id' => $switchId] = $languageSwitch($language, 'language'))
                                <li class="ss-card ss-onboarding-languages__result" id="language-{{ $language->code }}">
                                    <span class="ss-dictionary-code__flags ss-onboarding-languages__result-flags" aria-hidden="true">
                                        @foreach (array_slice($language->flagIcons(), 0, 4) as $flag)
                                            <x-admiro::flag :icon="$flag" />
                                        @endforeach
                                        @if (count($language->flagIcons()) > 4)
                                            <span class="ss-dictionary-code__more">+{{ count($language->flagIcons()) - 4 }}</span>
                                        @endif
                                    </span>
                                    <span class="ss-onboarding-languages__chip-text">
                                        <span class="ss-onboarding-languages__name">{{ $language->translatedName() }}</span>
                                        <span class="ss-table__sub">{{ implode(' · ', array_filter([$language->native_name !== $language->translatedName() ? $language->native_name : null, $language->code])) }}</span>
                                    </span>
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
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @elseif ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Installation)
                <section class="ss-onboarding-guide" aria-labelledby="ss-onboarding-guide-title">
                    <h3 class="ss-card__title" id="ss-onboarding-guide-title">{{ text('admiro::dashboard.onboarding_installation_guide_title') }}</h3>
                    <ol class="ss-onboarding-guide__stages">
                        @foreach ($installationGuide as ['key' => $stage, 'commands' => $commands])
                            <li class="ss-onboarding-guide__stage">
                                <span class="ss-onboarding-guide__number" aria-hidden="true">{{ $loop->iteration }}</span>
                                <div class="ss-onboarding-guide__body">
                                    <h4 class="ss-onboarding-guide__title">{{ text(\Simplysmart\Admiro\Panel\Onboarding\InstallationGuide::TEXTS . $stage . '.title') }}</h4>
                                    <p class="ss-muted ss-onboarding-guide__text">{{ text(\Simplysmart\Admiro\Panel\Onboarding\InstallationGuide::TEXTS . $stage . '.text') }}</p>
                                    <pre class="ss-onboarding-guide__code"><code>{{ implode("\n", $commands) }}</code></pre>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @else
                <p>
                    <a class="ss-button" href="{{ route($zone . $current->route(), $current->routeParameters()) }}"><x-admiro::icon :name="$current->icon()" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_open', ['name' => $stepTitle($current)]) }}</a>
                </p>
            @endif

            <div class="ss-onboarding-wizard__footer">
                <div class="ss-onboarding-wizard__footer-start">
                    @if ($previous !== null)
                        <a class="ss-button" href="{{ route($zone . 'onboarding.show', ['step' => $previous->value]) }}"><x-admiro::icon name="arrow-left" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_back') }}</a>
                    @endif
                    <span class="ss-badge ss-badge--outline ss-badge--{{ $currentState->tone() }}">{{ $currentState->label() }}</span>
                </div>

                @if ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages && $canManage)
                    <form method="POST" action="{{ route($zone . 'onboarding.confirm', ['step' => $current->value]) }}">
                        @csrf
                        <button class="ss-button ss-button--primary" type="submit">{{ $next === null ? text('admiro::dashboard.onboarding_confirm_last') : text('admiro::dashboard.onboarding_confirm') }}<x-admiro::icon name="arrow-right" class="ss-button__icon" /></button>
                    </form>
                @elseif ($next !== null)
                    <a class="ss-button ss-button--primary" href="{{ route($zone . 'onboarding.show', ['step' => $next->value]) }}">{{ text('admiro::dashboard.onboarding_next', ['number' => (string) (collect($checklist)->firstWhere('step', $next)['number'] ?? '')]) }}<x-admiro::icon name="arrow-right" class="ss-button__icon" /></a>
                @endif
            </div>
        </section>
    </div>
</x-admiro::zone.layout>
