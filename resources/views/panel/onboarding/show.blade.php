{{-- Kreator onboardingu (OnboardingController::show): nagłówek strony „Onboarding” z „Wstecz” nad tytułem (bez opisu), obok treści pionowa lista zadań jako karty-odnośniki (numer — dla zrobionych haczyk —, nazwa, opis tylko niezrobionych, a zrobione zamiast opisu „Zadanie N” krojem mono, i ikona; bieżące z aria-current="step"), karta bieżącego zadania: w nagłówku ikona, nazwa, a pod nią zamiast opisu plakietka stanu (obrys w kolorze stanu, OnboardingStepState::tone()) z datą zakończenia zrobionego zadania w środku (completedAt, mono); w prawym górnym rogu wyszukiwarka zadania (organizacje), pod nagłówkiem wpis słownika zadania (OnboardingStep::wiki(), x-admiro::wiki-tip), potem treść, a w stopce po lewej wypełnione ikony zrobionych zadań, po prawej przyciski z ikonami pozostałych niezakończonych zadań (nazwa w podpowiedzi) prowadzące do nich. Zadanie „Instalacja systemu i konto hero”: instrukcja etapami z poleceniami (Panel\Onboarding\InstallationGuide, $installationGuide: list<array{key: string, commands: list<string>}>). Zadanie „Języki”: panel włączonych języków (.ss-onboarding-languages: nagłówek, wiersz języka domyślnego z plakietką „Domyślny” i ustawieniami regionalnymi — kraj, waluta, podatki, święta z $regional, RegionalSettingsSummary; dopóki zadanie „Ustawienia regionalne” nie jest zrobione ($regionalConfigured), odnośnik do niego w przerywanej ramce, a brakujące podatki albo święta w przerywanych ramkach z „Ustaw” —, pod nim pozostałe języki po jednym w wierszu, panel/onboarding/language-row), pole wyszukiwania języka (także po kraju) na żywo (data-ss-live-search, js/live-search.js podmienia tylko #ss-languages-results) i — po wpisaniu `q` — wyniki w panelach (domyślny język pierwszy) (flagi krajów języka, nazwa, nazwa własna i kod, przełącznik; panel z kotwicą #language-<kod>), przełączniki zapisują się od razu, bez przycisku zapisu (data-ss-autosubmit; język domyślny — domyślny i wymagany — ma zamiast przełącznika plakietkę „Domyślny”, język interfejsu zablokowany), „Zatwierdź i dalej” w stopce zamyka zadanie. Zadanie „Organizacje”: wyszukiwarka bez etykiety w prawym górnym rogu karty z podpowiedziami (zakres katalogu) i menu „+” zakładania organizacji (admiro::search.actions); po wpisaniu `q` nad drzewem lista wyników; „Struktura organizacyjna” — wszystkie organizacje (licznik aktywne/wszystkie; wyłączone zostają w drzewie wyszarzone z plakietką stanu RecordStatus — wyłączenie niczego nie usuwa; domyślna organizacja operatora z plakietką „Domyślna” zamiast przełącznika) jako drzewo z cienkimi liniami i przeciąganiem (panel/onboarding/organization-row, Organizations\OrganizationTree, js/tree-dnd.js: jednostkę upuszczoną na aktywną organizację przenosi pod nią, na strefę pod drzewem — na najwyższy poziom; nazwa, nazwa skrócona i NIP, plakietki paczek — Organizations\OrganizationBadges, np. kooperacja —, strefy, właściciele jako awatary, przełącznik), wyniki `q` w panelach (#organization-<token>, nazwa prowadzi do edycji; wyłączoną organizację można przeciągnąć do drzewa aktywnych — ukryty formularz organizations.move z `activate` ją włącza i umieszcza pod celem albo na najwyższym poziomie), przełączniki zapisują się od razu; zadanie zamyka założenie organizacji. Pozostałe zadania: opis i przejście do strony panelu. $checklist: list<array{step: OnboardingStep, number: int, state: OnboardingStepState, done: bool, completedAt: ?CarbonImmutable}>; $current, ?$next: OnboardingStep; dla języków $term: string, $enabled i $languages: Collection<int, Language>, $canManage: bool; dla organizacji $term, $organizations: Collection<int, Organization> (wyniki), $all: Collection<int, Organization> (wszystkie), $activeCount: int, $tree: list<array{organization: Organization, children: list<array>}> (OrganizationTree z $all), $assignments: array<int, array{zones: list<ZoneDefinition>, owners: list<array{zone: ZoneDefinition, account: ZoneAccount}>}> (OrganizationAssignments), $badges: array<int, list<array{label: string, tone: string}>> (OrganizationBadges), $canManage, $canAssignOwners: bool, $organizationActions: list<NavigationItem>. --}}
@php
    $zone = 'admiro.';
    $stepTitle = static fn (\Simplysmart\Admiro\Panel\Enums\OnboardingStep $step): string => text('admiro::dashboard.onboarding_' . $step->value . '_title');
    $currentItem = collect($checklist)->firstWhere('step', $current);
    $currentState = $currentItem['state'] ?? \Simplysmart\Admiro\Panel\Enums\OnboardingStepState::NotStarted;
    $currentCompletedAt = $currentItem['completedAt'] ?? null;
    $remaining = array_values(array_filter($checklist, static fn (array $item): bool => ! $item['done'] && $item['step'] !== $current));
    $finished = array_values(array_filter($checklist, static fn (array $item): bool => $item['done'] && $item['step'] !== $current));
@endphp
<x-admiro::zone.layout :title="text('admiro::dashboard.onboarding_title')">
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
                                @if ($done)
                                    <span class="ss-onboarding-wizard__step-number">{{ text('admiro::dashboard.onboarding_step', ['number' => (string) $number]) }}</span>
                                @else
                                    <span class="ss-onboarding-wizard__step-lead">{{ text('admiro::dashboard.onboarding_' . $step->value . '_lead') }}</span>
                                @endif
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
                <div class="ss-onboarding-wizard__heading">
                    <h2 class="ss-onboarding-wizard__title" id="ss-onboarding-step-title">{{ $stepTitle($current) }}</h2>
                    <p class="ss-onboarding-wizard__status">
                        <span class="ss-badge ss-badge--outline ss-badge--{{ $currentState->tone() }}">{{ $currentState->label() }}@if ($currentCompletedAt !== null) <time class="ss-onboarding-wizard__completed" datetime="{{ $currentCompletedAt->toIso8601String() }}">{{ local_date($currentCompletedAt, 'D MMM YYYY') }}</time>@endif</span>
                    </p>
                </div>
                @if ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Organizations)
                    <form class="ss-search ss-onboarding-picker__search ss-onboarding-wizard__search" role="search" method="GET" action="{{ route($zone . 'onboarding.show', ['step' => $current->value]) }}" data-ss-search-suggest="{{ route($zone . 'search.suggest') }}">
                        <input type="hidden" name="in[]" value="{{ \Simplysmart\Admiro\Panel\Enums\SearchScope::Directory->value }}">
                        <label class="ss-sr-only" for="ss-organizations-search">{{ text('admiro::dashboard.onboarding_organizations_search_label') }}</label>
                        @if ($organizationActions !== [])
                            @include('admiro::search.actions', ['actions' => $organizationActions])
                        @endif
                        <x-admiro::icon name="search" class="ss-search__icon" />
                        <input class="ss-search__input" id="ss-organizations-search" name="q" type="search" value="{{ $term }}" placeholder="{{ text('admiro::dashboard.onboarding_organizations_search_placeholder') }}" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ss-organizations-suggestions" data-ss-search>
                        <div class="ss-search__suggestions" id="ss-organizations-suggestions" role="listbox" aria-label="{{ text('layout.search_suggestions') }}" hidden data-ss-search-suggestions></div>
                    </form>
                @endif
            </div>

            @if ($current->wiki() !== null)
                <x-admiro::wiki-tip :term="$current->wiki()" />
            @endif

            @if ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages)
                @php($languageSwitch = static fn (\Simplysmart\Admiro\Models\Dictionaries\Language $language, string $prefix): array => [
                    'locked' => $language->is_default || $language->is_interface,
                    'id' => $prefix . '-' . $language->code,
                ])

                @php($defaultLanguage = $enabled->first(static fn (\Simplysmart\Admiro\Models\Dictionaries\Language $language): bool => $language->is_default === true))
                @php($otherLanguages = $enabled->reject(static fn (\Simplysmart\Admiro\Models\Dictionaries\Language $language): bool => $language->is_default === true))

                <section class="ss-card ss-card--muted ss-onboarding-languages" aria-labelledby="ss-languages-enabled-title">
                    <header class="ss-onboarding-languages__header">
                        <h3 class="ss-card__title" id="ss-languages-enabled-title">{{ text('admiro::dashboard.onboarding_languages_enabled') }} <span class="ss-badge">{{ $enabled->count() }}</span></h3>
                    </header>
                    @if ($enabled->isEmpty())
                        <p class="ss-muted">{{ text('admiro::dashboard.onboarding_languages_enabled_empty') }}</p>
                    @endif
                    @if ($defaultLanguage !== null)
                        <div class="ss-onboarding-languages__default">
                            @include('admiro::panel.onboarding.language-row', ['language' => $defaultLanguage])
                            @if ($regionalConfigured)
                                <dl class="ss-onboarding-languages__regional" aria-label="{{ text('admiro::dashboard.onboarding_regional_title') }}">
                                    <div class="ss-onboarding-languages__regional-item">
                                        <dt>{{ text('admiro::dashboard.onboarding_regional_country') }}</dt>
                                        <dd>
                                            @if ($regional['country'] !== null)
                                                @if ($regional['country']->flag_icon !== null)
                                                    <x-admiro::flag :icon="$regional['country']->flag_icon" />
                                                @endif
                                                {{ $regional['country']->translatedName() }}
                                            @else
                                                —
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="ss-onboarding-languages__regional-item">
                                        <dt>{{ text('admiro::dashboard.onboarding_regional_currency') }}</dt>
                                        <dd>{{ $regional['currency'] !== null ? $regional['currency']->alpha_code_3 . ' · ' . $regional['currency']->translatedName() : '—' }}</dd>
                                    </div>
                                    <div @class(['ss-onboarding-languages__regional-item', 'ss-onboarding-languages__regional-item--missing' => $regional['tax'] === null])>
                                        <dt>{{ text('admiro::dashboard.onboarding_regional_taxes') }}</dt>
                                        <dd>
                                            @if ($regional['country'] === null)
                                                —
                                            @else
                                                <a class="ss-link" href="{{ route($zone . 'tax-rates.index', ['country' => $regional['country']->alpha_code_2]) }}">{{ $regional['tax'] !== null ? local_number((float) $regional['tax']->rate, 0) . '%' : text('admiro::dashboard.onboarding_regional_set') }}</a>
                                            @endif
                                        </dd>
                                    </div>
                                    <div @class(['ss-onboarding-languages__regional-item', 'ss-onboarding-languages__regional-item--missing' => $regional['holidays'] === 0])>
                                        <dt>{{ text('admiro::dashboard.onboarding_regional_holidays') }}</dt>
                                        <dd><a class="ss-link" href="{{ route($zone . 'holidays.index') }}">{{ $regional['holidays'] === 0 ? text('admiro::dashboard.onboarding_regional_set') : text('admiro::dashboard.onboarding_regional_holidays_count', ['count' => local_number($regional['holidays'])]) }}</a></dd>
                                    </div>
                                </dl>
                            @else
                                <a class="ss-onboarding-languages__regional ss-onboarding-languages__regional--missing" href="{{ route($zone . 'onboarding.show', ['step' => \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Settings->value]) }}">
                                    <x-admiro::icon name="locale" class="ss-onboarding-languages__regional-icon" />
                                    <span>
                                        <span class="ss-onboarding-languages__regional-title">{{ text('admiro::dashboard.onboarding_regional_title') }}</span>
                                        <span class="ss-table__sub">{{ text('admiro::dashboard.onboarding_regional_missing') }}</span>
                                    </span>
                                </a>
                            @endif
                        </div>
                    @endif
                    @if ($otherLanguages->isNotEmpty())
                        <ul class="ss-onboarding-languages__rows">
                            @foreach ($otherLanguages as $language)
                                <li>@include('admiro::panel.onboarding.language-row', ['language' => $language])</li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="ss-onboarding-picker__list" aria-labelledby="ss-languages-all-title">
                    <h3 class="ss-card__title" id="ss-languages-all-title">{{ text('admiro::dashboard.onboarding_languages_search_title') }}</h3>
                    <form class="ss-search ss-onboarding-picker__search" role="search" method="GET" action="{{ route($zone . 'onboarding.show', ['step' => $current->value]) }}" data-ss-live-search="ss-languages-results">
                        <label class="ss-sr-only" for="ss-languages-search">{{ text('admiro::dashboard.onboarding_languages_search_label') }}</label>
                        <x-admiro::icon name="search" class="ss-search__icon" />
                        <input class="ss-search__input" id="ss-languages-search" name="q" type="search" value="{{ $term }}" placeholder="{{ text('admiro::dashboard.onboarding_languages_search_placeholder') }}" autocomplete="off" aria-controls="ss-languages-results">
                    </form>

                    <div class="ss-onboarding-picker__live" id="ss-languages-results" aria-live="polite">
                        @if ($term === '')
                            <p class="ss-muted ss-onboarding-picker__hint">{{ text('admiro::dashboard.onboarding_languages_hint') }}</p>
                        @elseif ($languages->isEmpty())
                            <p class="ss-notice">{{ text('admiro::dashboard.onboarding_languages_none', ['term' => $term]) }}</p>
                        @else
                            <p class="ss-sr-only">{{ text('admiro::dashboard.onboarding_languages_results', ['term' => $term]) }}</p>
                            <ul class="ss-grid ss-grid--3 ss-onboarding-picker__results">
                                @foreach ($languages as $language)
                                    @php(['locked' => $locked, 'id' => $switchId] = $languageSwitch($language, 'language'))
                                    <li class="ss-card ss-onboarding-picker__result" id="language-{{ $language->code }}">
                                        <span class="ss-dictionary-code__flags ss-onboarding-picker__result-flags" aria-hidden="true">
                                            @foreach (array_slice($language->flagIcons(), 0, 4) as $flag)
                                                <x-admiro::flag :icon="$flag" />
                                            @endforeach
                                            @if (count($language->flagIcons()) > 4)
                                                <span class="ss-dictionary-code__more">+{{ count($language->flagIcons()) - 4 }}</span>
                                            @endif
                                        </span>
                                        <span class="ss-onboarding-picker__chip-text">
                                            <span class="ss-onboarding-picker__name">{{ $language->translatedName() }}</span>
                                            <span class="ss-table__sub">{{ implode(' · ', array_filter([$language->native_name !== $language->translatedName() ? $language->native_name : null, $language->code])) }}</span>
                                        </span>
                                        @if ($language->is_default)
                                            <span class="ss-badge ss-badge--outline ss-badge--info" title="{{ text('admiro::dashboard.onboarding_language_locked') }}">{{ text('admiro::dashboard.onboarding_language_default') }}</span>
                                        @elseif ($canManage)
                                            <form method="POST" action="{{ route($zone . 'dictionaries.toggle', ['dictionary' => 'languages', 'code' => $language->code]) }}" data-ss-autosubmit>
                                                @csrf
                                                <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_language_switch', ['name' => $language->translatedName()])" :checked="$language->is_active" :disabled="$locked" :title="$locked ? text('admiro::dashboard.onboarding_language_locked') : null" hide-label />
                                            </form>
                                        @else
                                            <span class="ss-badge">{{ $language->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>
            @elseif ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Organizations)
                @php($organizationSwitch = static fn (\Simplysmart\Admiro\Models\Organization $organization, string $prefix): array => [
                    'locked' => $organization->id === \Simplysmart\Admiro\Models\Organization::OPERATOR_ID,
                    'id' => $prefix . '-' . $organization->token,
                    'details' => implode(' · ', array_filter([$organization->short_name, $organization->nip !== null ? text('admiro::search.nip', ['nip' => $organization->nip]) : null])),
                ])

                @if ($term !== '')
                    <section class="ss-onboarding-picker__list" aria-labelledby="ss-organizations-all-title">
                        <h3 class="ss-card__title" id="ss-organizations-all-title">{{ text('admiro::dashboard.onboarding_organizations_results', ['term' => $term]) }}</h3>
                        @if ($organizations->isEmpty())
                            <p class="ss-notice">{{ text('admiro::dashboard.onboarding_organizations_none', ['term' => $term]) }}</p>
                        @else
                            <ul class="ss-grid ss-grid--3 ss-onboarding-picker__results">
                                @foreach ($organizations as $organization)
                                    @php(['locked' => $locked, 'id' => $switchId, 'details' => $details] = $organizationSwitch($organization, 'organization'))
                                    @php($draggable = $canManage && ! $locked && ! $organization->is_active && ! $organization->isDepartment())
                                    <li class="ss-card ss-onboarding-picker__result" id="organization-{{ $organization->token }}" @if ($draggable) draggable="true" data-ss-tree-item="{{ $organization->token }}" title="{{ text('admiro::dashboard.onboarding_organization_drag_hint') }}" @endif>
                                        @if ($draggable)
                                            <form method="POST" action="{{ route($zone . 'organizations.move', ['organization' => $organization->token]) }}" hidden data-ss-tree-move>
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="parent" value="">
                                                <input type="hidden" name="activate" value="1">
                                            </form>
                                        @endif
                                        <x-admiro::icon name="building" class="ss-icon--tile ss-onboarding-picker__result-flags" />
                                        <span class="ss-onboarding-picker__chip-text">
                                            @if ($canManage)
                                                <a class="ss-onboarding-picker__name" href="{{ route($zone . 'organizations.edit', ['organization' => $organization->token]) }}">{{ $organization->name }}</a>
                                            @else
                                                <span class="ss-onboarding-picker__name">{{ $organization->name }}</span>
                                            @endif
                                            @if ($details !== '')
                                                <span class="ss-table__sub">{{ $details }}</span>
                                            @endif
                                        </span>
                                        @if ($locked)
                                            <span class="ss-badge ss-badge--outline ss-badge--info" title="{{ text('admiro::dashboard.onboarding_organization_locked') }}">{{ text('admiro::dashboard.onboarding_organization_default') }}</span>
                                        @elseif ($canManage)
                                            <form method="POST" action="{{ route($zone . 'organizations.toggle', ['organization' => $organization->token]) }}" data-ss-autosubmit>
                                                @csrf
                                                <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_organization_switch', ['name' => $organization->name])" :checked="$organization->is_active" hide-label states />
                                            </form>
                                        @else
                                            <span class="ss-badge">{{ $organization->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endif

                <section class="ss-card ss-card--muted ss-onboarding-picker__enabled" aria-labelledby="ss-organizations-enabled-title">
                    <h3 class="ss-card__title" id="ss-organizations-enabled-title">{{ text('admiro::dashboard.onboarding_organizations_enabled') }} <span class="ss-badge" title="{{ text('admiro::dashboard.onboarding_organizations_active_count', ['active' => $activeCount, 'all' => $all->count()]) }}">{{ $activeCount }}/{{ $all->count() }}</span></h3>
                    @if ($all->isEmpty())
                        <p class="ss-muted">{{ text('admiro::dashboard.onboarding_organizations_enabled_empty') }}</p>
                    @else
                        <div class="ss-onboarding-organizations__tree" data-ss-tree>
                            <ul class="ss-onboarding-organizations">
                                @foreach ($tree as $node)
                                    @include('admiro::panel.onboarding.organization-row', ['node' => $node])
                                @endforeach
                            </ul>
                            @if ($canManage)
                                <p class="ss-onboarding-organizations__root" data-ss-tree-root>{{ text('admiro::dashboard.onboarding_organization_root_drop') }}</p>
                            @endif
                        </div>
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
                    @if ($finished !== [])
                        <nav class="ss-onboarding-wizard__remaining ss-onboarding-wizard__finished" aria-label="{{ text('admiro::dashboard.onboarding_finished') }}">
                            <span class="ss-onboarding-wizard__remaining-label">{{ text('admiro::dashboard.onboarding_finished') }}</span>
                            @foreach ($finished as ['step' => $step, 'number' => $number])
                                <a class="ss-icon-button ss-onboarding-wizard__finished-link" href="{{ route($zone . 'onboarding.show', ['step' => $step->value]) }}" title="{{ text('admiro::dashboard.onboarding_step', ['number' => (string) $number]) }}: {{ $stepTitle($step) }}">
                                    <x-admiro::icon :name="$step->icon()" class="ss-icon-button__icon" />
                                    <span class="ss-sr-only">{{ text('admiro::dashboard.onboarding_step', ['number' => (string) $number]) }}: {{ $stepTitle($step) }}</span>
                                </a>
                            @endforeach
                        </nav>
                    @endif
                    @if ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages && $canManage)
                        <form method="POST" action="{{ route($zone . 'onboarding.confirm', ['step' => $current->value]) }}">
                            @csrf
                            <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ $next === null ? text('admiro::dashboard.onboarding_confirm_last') : text('admiro::dashboard.onboarding_confirm') }}</button>
                        </form>
                    @endif
                </div>

                @if ($remaining !== [])
                    <nav class="ss-onboarding-wizard__remaining" aria-label="{{ text('admiro::dashboard.onboarding_remaining') }}">
                        <span class="ss-onboarding-wizard__remaining-label">{{ text('admiro::dashboard.onboarding_remaining') }}</span>
                        @foreach ($remaining as ['step' => $step, 'number' => $number])
                            <a class="ss-icon-button ss-onboarding-wizard__remaining-link" href="{{ route($zone . 'onboarding.show', ['step' => $step->value]) }}" title="{{ text('admiro::dashboard.onboarding_step', ['number' => (string) $number]) }}: {{ $stepTitle($step) }}">
                                <x-admiro::icon :name="$step->icon()" class="ss-icon-button__icon" />
                                <span class="ss-sr-only">{{ text('admiro::dashboard.onboarding_step', ['number' => (string) $number]) }}: {{ $stepTitle($step) }}</span>
                            </a>
                        @endforeach
                    </nav>
                @endif
            </div>
        </section>
    </div>
</x-admiro::zone.layout>
