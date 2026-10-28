{{-- Kreator onboardingu (OnboardingController::show): nagłówek strony „Onboarding” z „Wstecz” nad tytułem (bez opisu), obok treści pionowa lista zadań jako karty-odnośniki (numer — dla zrobionych haczyk —, nazwa, opis tylko niezrobionych, a zrobione zamiast opisu „Zadanie N” krojem mono, i ikona; bieżące z aria-current="step"), karta bieżącego zadania: w nagłówku ikona, nazwa, a pod nią zamiast opisu plakietka stanu (obrys w kolorze stanu, OnboardingStepState::tone()) — u zrobionego zadania „Ostatnia aktualizacja” z datą zakończenia (completedAt, mono); w prawym górnym rogu wyszukiwarka zadania (organizacje), pod nagłówkiem wpis słownika zadania (OnboardingStep::wiki(), x-admiro::wiki-tip), potem treść, a w stopce po lewej wypełnione ikony zrobionych zadań, po prawej przyciski z ikonami pozostałych niezakończonych zadań (nazwa w podpowiedzi) prowadzące do nich. Zadanie „Instalacja systemu i konto hero”: instrukcja etapami z poleceniami (Panel\Onboarding\InstallationGuide, $installationGuide: list<array{key: string, commands: list<string>}>). Zadanie „Języki”: teczki obok siebie jak zakładki (.ss-folders; w stopkach liczniki i data ostatniej aktualizacji zadania — w nagłówku wtedy sam stan —, js/tabs.js; zakładka wybrana też kotwicą #translations-upload): „Język interfejsu” i — z uprawnieniem — „Tłumaczenia” (w pasie przycisków szablon do uzupełnienia, eksport tłumaczeń domyślnych i wszystkich oraz „Prześlij tłumaczenia” pokazywany dopiero po sprawdzeniu wybranych plików; przeciąganie plików JSON na cały arkusz, js/file-drop.js; po błędzie przesyłania wybrana); każda teczka (.ss-folder) w dwóch warstwach: tylna ciemniejsza — zakładka z samym tytułem i pod nią pas __toolbar z przełącznikiem widoku Aktywne / Niedostępne (ikona i napis) — i jaśniejszy arkusz __body z treścią — .ss-tabs--slider, js/view-switch.js podmienia #ss-languages-workspace, `view=hidden` ($languageView, $hidden: wyłączone języki) pokazuje siatkę kafelków z flagą, nazwą, kodem pod nią i przyciskiem zasilania —, wiersz języka domyślnego z plakietką „Domyślny” i ustawieniami regionalnymi — kraj, waluta, podatki, święta z $regional, RegionalSettingsSummary; dopóki zadanie „Ustawienia regionalne” nie jest zrobione ($regionalConfigured), odnośnik do niego w przerywanej ramce, a brakujące podatki albo święta w przerywanych ramkach z „Ustaw” —, pod nim pozostałe języki po jednym w wierszu, panel/onboarding/language-row), pole wyszukiwania języka (także po kraju) na żywo (data-ss-live-search, js/live-search.js podmienia tylko #ss-languages-results) i — po wpisaniu `q` — wyniki w panelach (domyślny język pierwszy) (flagi krajów języka, nazwa, nazwa własna i kod, przełącznik; panel z kotwicą #language-<kod>), przełączniki zapisują się od razu, bez przycisku zapisu (data-ss-autosubmit; język domyślny — domyślny i wymagany — ma zamiast przełącznika plakietkę „Domyślny”, język interfejsu zablokowany), „Zatwierdź i dalej” w stopce zamyka zadanie; przy „+” w wyszukiwarce menu z błyskawicą (.ss-search__quick) z importem (otwiera sekcję „Tłumaczenia” pod panelem: #translations-upload, OnboardingStep::TRANSLATIONS_ANCHOR; po błędzie przesyłania otwarta) i eksportem (onboarding.translations.export — szablony wszystkich włączonych języków w jednym JSON-ie) z przeciąganiem plików JSON (js/file-drop.js) do onboarding.translations.import. Zadanie „Organizacje”: wyszukiwarka bez etykiety w prawym górnym rogu karty z podpowiedziami (zakres katalogu) i menu „+” zakładania organizacji (admiro::search.actions); po wpisaniu `q` nad drzewem lista wyników; „Struktura organizacyjna” — wszystkie organizacje (licznik aktywne/wszystkie; wyłączone zostają w drzewie wyszarzone z plakietką stanu RecordStatus — wyłączenie niczego nie usuwa; domyślna organizacja operatora z plakietką „Domyślna” zamiast przełącznika) jako drzewo z cienkimi liniami i przeciąganiem (panel/onboarding/organization-row, Organizations\OrganizationTree, js/tree-dnd.js: jednostkę upuszczoną na aktywną organizację przenosi pod nią, na strefę pod drzewem — na najwyższy poziom; nazwa, nazwa skrócona i NIP, plakietki paczek — Organizations\OrganizationBadges, np. kooperacja —, strefy, właściciele jako awatary, przełącznik), wyniki `q` w panelach (#organization-<token>, nazwa prowadzi do edycji; wyłączoną organizację można przeciągnąć do drzewa aktywnych — ukryty formularz organizations.move z `activate` ją włącza i umieszcza pod celem albo na najwyższym poziomie), przełączniki zapisują się od razu; zadanie zamyka założenie organizacji. Zadania „Kraje”, „Waluty”, „Kalendarz i święta” i „Stawki podatkowe” (Panel\Onboarding\DictionarySteps) na wzór „Języków”: wyszukiwarka w rogu (zakres słowników), po wpisaniu `q` wyniki nad panelem, panel włączonych wpisów z licznikiem włączone/wszystkie (domyślny pierwszy, z plakietką), wiersz panel/onboarding/dictionary-row z faktami zadania i akcjami zapisywanymi od razu (przełącznik z gałką dla krajów i walut, kolumna synchronizacji kursów NBP przy walucie, „Pobierz święta” przy kraju — obie wracają do kreatora; bez kraju lub waluty domyślnej przerywane pole z odnośnikiem do zadania „Język i tłumaczenia”). Zadanie „Profile i uprawnienia”: wyszukiwarka administratorów w prawym górnym rogu (zakres katalogu), po wpisaniu `q` wyniki nad panelami (panel/onboarding/administrator-row), panel „Profile uprawnień” — kafelek każdej roli strefy z liczbą uprawnień i osób — i panel „Administratorzy” — wiersz każdego administratora z rolami jako przełącznikami z gałką ON / OFF zapisywanymi od razu (administrators.roles.toggle; własne konto tylko z plakietkami ról) i odnośnik do pełnej listy. Pozostałe zadania: opis i przejście do strony panelu. $checklist: list<array{step: OnboardingStep, number: int, state: OnboardingStepState, done: bool, completedAt: ?CarbonImmutable}>; $current, ?$next: OnboardingStep; dla języków $term: string, $enabled i $languages: Collection<int, Language>, $canManage: bool; dla organizacji $term, $organizations: Collection<int, Organization> (wyniki), $all: Collection<int, Organization> (wszystkie), $activeCount: int, $tree: list<array{organization: Organization, children: list<array>}> (OrganizationTree z $all), $assignments: array<int, array{zones: list<ZoneDefinition>, owners: list<array{zone: ZoneDefinition, account: ZoneAccount}>}> (OrganizationAssignments), $badges: array<int, list<array{label: string, tone: string}>> (OrganizationBadges), $canManage, $canAssignOwners: bool, $organizationActions: list<NavigationItem>; dla zadań słownikowych $term, $dictionary: Localization\Dictionary, $all, $enabled i $found: Collection<int, Country|Currency>, $facts: array<string, array>, $year: int, $canManage; dla profili $term, $administrators i $found: list<ZoneAccount>, $rolesOf: array<int|string, list<string>> (Auth\AccountKey konta → role), $availableRoles, $profiles z liczbą uprawnień z ZoneRoleNames::permissionCounts(): list<string>, $profiles: list<array{role: string, permissions: int, members: int}>, $self: ZoneAccount, $canManage: bool. --}}
@php
    $zone = 'admiro.';
    $stepTitle = static fn (\Simplysmart\Admiro\Panel\Enums\OnboardingStep $step): string => text('admiro::dashboard.onboarding_' . $step->value . '_title');
    $currentItem = collect($checklist)->firstWhere('step', $current);
    $currentState = $currentItem['state'] ?? \Simplysmart\Admiro\Panel\Enums\OnboardingStepState::NotStarted;
    $currentCompletedAt = $currentItem['completedAt'] ?? null;
    $updateInFooter = $current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages;
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
                        <span class="ss-badge ss-badge--outline ss-badge--{{ $currentState->tone() }}">{{ $currentCompletedAt !== null && ! $updateInFooter ? text('admiro::dashboard.onboarding_last_update') : $currentState->label() }}@if ($currentCompletedAt !== null && ! $updateInFooter) <time class="ss-onboarding-wizard__completed" datetime="{{ $currentCompletedAt->toIso8601String() }}">{{ local_date($currentCompletedAt, 'D MMM YYYY') }}</time>@endif</span>
                    </p>
                </div>
                @php
                    $taskSearch = match ($current) {
                        \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Organizations => ['id' => 'organizations', 'scope' => \Simplysmart\Admiro\Panel\Enums\SearchScope::Directory, 'actions' => $wizardActions],
                        \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages => ['id' => 'languages', 'scope' => \Simplysmart\Admiro\Panel\Enums\SearchScope::Languages, 'actions' => $wizardActions],
                        \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Administrators => ['id' => 'admins', 'scope' => \Simplysmart\Admiro\Panel\Enums\SearchScope::Directory, 'actions' => $wizardActions],
                        \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Countries, \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Calendar, \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Taxes => ['id' => 'countries', 'scope' => \Simplysmart\Admiro\Panel\Enums\SearchScope::Dictionaries, 'actions' => $wizardActions],
                        \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Currencies => ['id' => 'currencies', 'scope' => \Simplysmart\Admiro\Panel\Enums\SearchScope::Dictionaries, 'actions' => $wizardActions],
                        default => null,
                    };
                @endphp
                @if ($taskSearch !== null)
                    <form class="ss-search ss-onboarding-picker__search ss-onboarding-wizard__search" role="search" method="GET" action="{{ route($zone . 'onboarding.show', ['step' => $current->value]) }}" data-ss-search-suggest="{{ route($zone . 'search.suggest') }}">
                        <input type="hidden" name="in[]" value="{{ $taskSearch['scope']->value }}">
                        <label class="ss-sr-only" for="ss-{{ $taskSearch['id'] }}-search">{{ text('admiro::dashboard.onboarding_' . $taskSearch['id'] . '_search_label') }}</label>
                        @if ($taskSearch['actions'] !== [])
                            @include('admiro::search.actions', ['actions' => $taskSearch['actions']])
                        @endif
                        @if ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages && $canManage)
                            <details class="ss-search__menu ss-search__actions ss-search__quick" data-ss-dropdown>
                                <summary class="ss-search__menu-toggle" title="{{ text('admiro::dashboard.onboarding_translations_quick') }}">
                                    <x-admiro::icon name="zap" class="ss-search__menu-icon" />
                                    <span class="ss-sr-only">{{ text('admiro::dashboard.onboarding_translations_quick') }}</span>
                                </summary>
                                <div class="ss-search__menu-panel">
                                    <p class="ss-search__menu-title">{{ text('admiro::dashboard.onboarding_translations_quick') }}</p>
                                    <ul class="ss-account-menu__list">
                                        <li><a class="ss-account-menu__link" href="#{{ \Simplysmart\Admiro\Panel\Enums\OnboardingStep::TRANSLATIONS_ANCHOR }}" data-ss-dropdown-close><x-admiro::icon name="upload" class="ss-account-menu__icon" />{{ text('admiro::dashboard.onboarding_translations_import') }}</a></li>
                                        <li><a class="ss-account-menu__link" href="{{ route($zone . 'onboarding.translations.export') }}" download data-ss-dropdown-close><x-admiro::icon name="download" class="ss-account-menu__icon" />{{ text('admiro::dashboard.onboarding_translations_export') }}</a></li>
                                    </ul>
                                </div>
                            </details>
                        @endif
                        <x-admiro::icon name="search" class="ss-search__icon" />
                        <input class="ss-search__input" id="ss-{{ $taskSearch['id'] }}-search" name="q" type="search" value="{{ $term }}" placeholder="{{ text('admiro::dashboard.onboarding_' . $taskSearch['id'] . '_search_placeholder') }}" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="ss-{{ $taskSearch['id'] }}-suggestions" data-ss-search>
                        <div class="ss-search__suggestions" id="ss-{{ $taskSearch['id'] }}-suggestions" role="listbox" aria-label="{{ text('layout.search_suggestions') }}" hidden data-ss-search-suggestions></div>
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

                @if ($term !== '')
                    <section class="ss-onboarding-picker__list" aria-labelledby="ss-languages-all-title">
                        <h3 class="ss-card__title" id="ss-languages-all-title">{{ text('admiro::dashboard.onboarding_languages_results', ['term' => $term]) }}</h3>
                        @if ($languages->isEmpty())
                            <p class="ss-notice">{{ text('admiro::dashboard.onboarding_languages_none', ['term' => $term]) }}</p>
                        @else
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
                                        <x-admiro::default-badge :label="text('admiro::dashboard.onboarding_language_default')" :title="text('admiro::dashboard.onboarding_language_locked')" />
                                    @elseif ($canManage)
                                        <form method="POST" action="{{ route($zone . 'dictionaries.toggle', ['dictionary' => 'languages', 'code' => $language->code]) }}" data-ss-autosubmit>
                                            @csrf
                                            <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_language_switch', ['name' => $language->translatedName()])" :checked="$language->is_active" :disabled="$locked" :title="$locked ? text('admiro::dashboard.onboarding_language_locked') : null" hide-label knob />
                                        </form>
                                    @else
                                        <span class="ss-badge">{{ $language->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        @endif
                    </section>
                @endif

                @php($translationErrors = ['translations', 'files', 'files.*'])
                @php($translationsOpen = $canManage && $errors->hasAny($translationErrors))
                @php($languagesUrl = fn (string $view, ?string $layout): string => route($zone . 'onboarding.show', array_filter(['step' => $current->value, 'view' => $view === 'active' ? null : $view, 'layout' => $layout === null || $layout === ($view === 'active' ? 'list' : 'grid') ? null : $layout])))
                <div class="ss-folders" data-ss-tabs>
                    <div class="ss-folders__tabs" role="tablist" aria-label="{{ text('admiro::dashboard.onboarding_languages_folders') }}" hidden data-ss-tabs-list>
                        <button class="ss-folder__tab" type="button" role="tab" id="ss-languages-enabled-title" aria-controls="ss-folder-languages" aria-selected="{{ $translationsOpen ? 'false' : 'true' }}" data-ss-tab><span class="ss-card__title">{{ text('admiro::dashboard.onboarding_languages_enabled') }}</span></button>
                        @if ($canManage)
                            <button class="ss-folder__tab" type="button" role="tab" id="ss-translations-title" aria-controls="{{ \Simplysmart\Admiro\Panel\Enums\OnboardingStep::TRANSLATIONS_ANCHOR }}" aria-selected="{{ $translationsOpen ? 'true' : 'false' }}" data-ss-tab><span class="ss-card__title">{{ text('admiro::dashboard.onboarding_translations_title') }}</span></button>
                        @endif
                    </div>

                    <section class="ss-folder" id="ss-folder-languages" role="tabpanel" aria-labelledby="ss-languages-enabled-title" data-ss-tab-panel>
                        <div class="ss-folder__back">
                            <div class="ss-folder__toolbar">
                                <nav class="ss-tabs ss-tabs--slider ss-onboarding-languages__views" id="ss-languages-views" aria-label="{{ text('admiro::dashboard.onboarding_languages_views') }}" data-ss-view-switch="ss-languages-workspace">
                                    @foreach (['active' => 'eye', 'hidden' => 'eye-off'] as $view => $icon)
                                        <a class="ss-tabs__link" href="{{ $languagesUrl($view, $languageLayoutChosen ? $languageLayout : null) }}" aria-current="{{ $languageView === $view ? 'true' : 'false' }}" data-ss-view><x-admiro::icon :name="$icon" class="ss-tabs__icon" />{{ text('admiro::dashboard.onboarding_languages_view_' . $view) }}</a>
                                    @endforeach
                                </nav>
                                <div class="ss-folder__toolbar-end ss-onboarding-languages__layout">
                                    <span class="ss-onboarding-languages__layout-label" id="ss-languages-layouts-label">{{ text('admiro::dashboard.onboarding_languages_layouts') }}</span>
                                    <nav class="ss-tabs ss-tabs--slider ss-onboarding-languages__views ss-onboarding-languages__views--icons" id="ss-languages-layouts" aria-labelledby="ss-languages-layouts-label" data-ss-view-switch="ss-languages-workspace">
                                        @foreach (['list' => 'list', 'grid' => 'grid'] as $layout => $icon)
                                            <a class="ss-tabs__link" href="{{ $languagesUrl($languageView, $layout) }}" title="{{ text('admiro::dashboard.onboarding_languages_layout_' . $layout) }}" aria-current="{{ $languageLayout === $layout ? 'true' : 'false' }}" data-ss-view><x-admiro::icon :name="$icon" class="ss-tabs__icon" /><span class="ss-sr-only">{{ text('admiro::dashboard.onboarding_languages_layout_' . $layout) }}</span></a>
                                        @endforeach
                                    </nav>
                                </div>
                            </div>
                            <div class="ss-folder__body">
                                @error('regional')
                                    <p class="ss-field__error" role="alert">{{ $message }}</p>
                                @enderror
                                <div class="ss-onboarding-languages__workspace" id="ss-languages-workspace">
                                    @if ($languageView === 'hidden')
                                        @if ($hidden->isEmpty())
                                            <p class="ss-muted">{{ text('admiro::dashboard.onboarding_languages_hidden_empty') }}</p>
                                        @else
                                            <ul @class(['ss-onboarding-languages__grid', 'ss-onboarding-languages__grid--list' => $languageLayout === 'list'])>
                                                @foreach ($hidden as $language)
                                                    <li class="ss-onboarding-languages__tile" id="hidden-language-{{ $language->code }}">
                                                        <span class="ss-onboarding-languages__tile-main">
                                                            <span class="ss-onboarding-picker__flags" aria-hidden="true">
                                                                @foreach (array_slice($language->flagIcons(), 0, 1) as $flag)
                                                                    <x-admiro::flag :icon="$flag" />
                                                                @endforeach
                                                            </span>
                                                            <span class="ss-onboarding-languages__tile-text">
                                                                <span class="ss-onboarding-picker__name">{{ $language->translatedName() }}</span>
                                                                <span class="ss-table__sub">{{ $language->code }}</span>
                                                            </span>
                                                        </span>
                                                        @if ($canManage)
                                                            <form method="POST" action="{{ route($zone . 'dictionaries.toggle', ['dictionary' => 'languages', 'code' => $language->code]) }}" data-ss-autosubmit>
                                                                @csrf
                                                                <x-admiro::switch :id="'hidden-language-' . $language->code . '-switch'" :label="text('admiro::dashboard.onboarding_language_switch', ['name' => $language->translatedName()])" :checked="false" hide-label knob />
                                                            </form>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @elseif ($enabled->isEmpty())
                                        <p class="ss-muted">{{ text('admiro::dashboard.onboarding_languages_enabled_empty') }}</p>
                                    @else
                                        <ul @class(['ss-onboarding-languages__rows', 'ss-onboarding-languages__rows--grid' => $languageLayout === 'grid'])>
                                            @foreach ($enabled as $language)
                                                <li>@include('admiro::panel.onboarding.language-row', ['language' => $language])</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                            <footer class="ss-folder__footer">
                                <span class="ss-folder__count"><x-admiro::icon name="eye" class="ss-folder__count-icon" />{{ text('admiro::dashboard.onboarding_languages_view_active') }} <span class="ss-badge">{{ local_number($enabled->count()) }}</span></span>
                                <span class="ss-folder__count"><x-admiro::icon name="eye-off" class="ss-folder__count-icon" />{{ text('admiro::dashboard.onboarding_languages_view_hidden') }} <span class="ss-badge">{{ local_number($hidden->count()) }}</span></span>
                                @if ($currentCompletedAt !== null)
                                    <span class="ss-folder__count ss-folder__updated"><x-admiro::icon name="history" class="ss-folder__count-icon" />{{ text('admiro::dashboard.onboarding_last_update') }} <time datetime="{{ $currentCompletedAt->toIso8601String() }}">{{ local_date($currentCompletedAt, 'D MMM YYYY') }}</time></span>
                                @endif
                            </footer>
                        </div>
                    </section>

                    @if ($canManage)
                        <section class="ss-folder ss-onboarding-translations" id="{{ \Simplysmart\Admiro\Panel\Enums\OnboardingStep::TRANSLATIONS_ANCHOR }}" role="tabpanel" aria-labelledby="ss-translations-title" data-ss-tab-panel>
                            <div class="ss-folder__back">
                                <div class="ss-folder__toolbar">
                                    <a class="ss-button ss-button--sm" href="{{ route($zone . 'onboarding.translations.template') }}" download title="{{ text('admiro::dashboard.onboarding_translations_template_hint') }}"><x-admiro::icon name="clipboard" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_translations_template') }}</a>
                                    <a class="ss-button ss-button--sm" href="{{ route($zone . 'onboarding.translations', ['code' => $sourceLanguage]) }}" download><x-admiro::icon name="download" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_translations_export_default') }}</a>
                                    <a class="ss-button ss-button--sm" href="{{ route($zone . 'onboarding.translations.export') }}" download><x-admiro::icon name="download" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_translations_export') }}</a>
                                    <button class="ss-button ss-button--primary ss-button--sm ss-onboarding-translations__submit" type="submit" form="ss-translations-form" hidden data-ss-file-drop-submit><x-admiro::icon name="upload" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_translations_submit') }}</button>
                                </div>
                                <div class="ss-folder__body">
                                    @if ($errors->hasAny($translationErrors))
                                        <p class="ss-field__error" role="alert">{{ $errors->first('translations') ?: ($errors->first('files') ?: $errors->first('files.*')) }}</p>
                                    @endif
                                    <form class="ss-onboarding-translations__form" id="ss-translations-form" method="POST" action="{{ route($zone . 'onboarding.translations.import') }}" enctype="multipart/form-data" data-ss-file-drop data-ss-file-drop-max="{{ \Simplysmart\Admiro\Translation\TranslationImport::MAX_FILES }}" data-ss-file-drop-valid="{{ text('admiro::dashboard.onboarding_translations_checked') }}" data-ss-file-drop-invalid="{{ text('admiro::dashboard.onboarding_translations_rejected') }}">
                                        @csrf
                                        <label class="ss-onboarding-translations__drop" for="ss-translations-files" data-ss-file-drop-zone>
                                            <x-admiro::icon name="upload" class="ss-onboarding-translations__icon" />
                                            <span class="ss-onboarding-translations__drop-text">{{ text('admiro::dashboard.onboarding_translations_drop') }}</span>
                                            <span class="ss-table__sub">{{ text('admiro::dashboard.onboarding_translations_formats', ['count' => \Simplysmart\Admiro\Translation\TranslationImport::MAX_FILES, 'size' => local_number(\Simplysmart\Admiro\Translation\TranslationImport::MAX_KILOBYTES)]) }}</span>
                                            <input class="ss-onboarding-translations__input" id="ss-translations-files" name="files[]" type="file" accept=".json,application/json" multiple required>
                                        </label>
                                        <ul class="ss-onboarding-translations__files" aria-label="{{ text('admiro::dashboard.onboarding_translations_chosen') }}" aria-live="polite" hidden data-ss-file-drop-list></ul>
                                        <noscript><button class="ss-button ss-button--primary ss-button--sm" type="submit"><x-admiro::icon name="upload" class="ss-button__icon" />{{ text('admiro::dashboard.onboarding_translations_submit') }}</button></noscript>
                                    </form>
                                </div>
                                <footer class="ss-folder__footer">
                                    <span class="ss-folder__count"><x-admiro::icon name="languages" class="ss-folder__count-icon" />{{ text('admiro::dashboard.onboarding_translations_keywords') }} <span class="ss-badge">{{ local_number(max([0, ...array_column($keywords, 'all')])) }}</span></span>
                                    <span class="ss-folder__count"><x-admiro::icon name="flag" class="ss-folder__count-icon" />{{ text('admiro::dashboard.onboarding_translations_languages') }} <span class="ss-badge">{{ local_number($enabled->count()) }}</span></span>
                                    @if ($currentCompletedAt !== null)
                                        <span class="ss-folder__count ss-folder__updated"><x-admiro::icon name="history" class="ss-folder__count-icon" />{{ text('admiro::dashboard.onboarding_last_update') }} <time datetime="{{ $currentCompletedAt->toIso8601String() }}">{{ local_date($currentCompletedAt, 'D MMM YYYY') }}</time></span>
                                    @endif
                                </footer>
                            </div>
                        </section>
                    @endif
                </div>
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
                                            <x-admiro::default-badge :label="text('admiro::dashboard.onboarding_organization_default')" :title="text('admiro::dashboard.onboarding_organization_locked')" />
                                        @elseif ($canManage)
                                            <form method="POST" action="{{ route($zone . 'organizations.toggle', ['organization' => $organization->token]) }}" data-ss-autosubmit>
                                                @csrf
                                                <x-admiro::switch :id="$switchId" :label="text('admiro::dashboard.onboarding_organization_switch', ['name' => $organization->name])" :checked="$organization->is_active" hide-label knob />
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
            @elseif (in_array($current, [\Simplysmart\Admiro\Panel\Enums\OnboardingStep::Countries, \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Currencies, \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Calendar, \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Taxes], true))
                @if ($term !== '')
                    <section class="ss-onboarding-picker__list" aria-labelledby="ss-dictionary-results-title">
                        <h3 class="ss-card__title" id="ss-dictionary-results-title">{{ text('admiro::dashboard.onboarding_dictionary_results', ['term' => $term]) }}</h3>
                        @if ($found->isEmpty())
                            <p class="ss-notice">{{ text('admiro::dashboard.onboarding_dictionary_none', ['term' => $term]) }}</p>
                        @else
                            <ul class="ss-onboarding-languages__rows">
                                @foreach ($found as $entry)
                                    @include('admiro::panel.onboarding.dictionary-row', ['prefix' => 'found'])
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endif

                <section class="ss-card ss-card--muted ss-onboarding-languages" aria-labelledby="ss-dictionary-title">
                    <header class="ss-onboarding-languages__header ss-onboarding-dictionary__header">
                        <h3 class="ss-card__title" id="ss-dictionary-title">{{ text('admiro::dashboard.onboarding_' . $current->value . '_panel') }} <span class="ss-badge" title="{{ text('admiro::dashboard.onboarding_dictionary_count', ['active' => $enabled->count(), 'all' => $all->count()]) }}">{{ $enabled->count() }}/{{ $all->count() }}</span></h3>
                    </header>
                    @if ($defaultMissing && in_array($current, [\Simplysmart\Admiro\Panel\Enums\OnboardingStep::Countries, \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Currencies], true))
                        <a class="ss-onboarding-dictionary__missing" href="{{ route($zone . 'onboarding.show', ['step' => \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Languages->value]) }}">
                            <x-admiro::icon name="locale" class="ss-onboarding-dictionary__missing-icon" />
                            <span>
                                <span class="ss-onboarding-dictionary__missing-title">{{ text('admiro::dashboard.onboarding_' . $current->value . '_default_missing') }}</span>
                                <span class="ss-table__sub">{{ text('admiro::dashboard.onboarding_default_missing_hint') }}</span>
                            </span>
                        </a>
                    @endif
                    @foreach (['dictionary', 'holiday', 'sync_from'] as $errorField)
                        @error($errorField)
                            <p class="ss-field__error" role="alert">{{ $message }}</p>
                        @enderror
                    @endforeach
                    @if ($enabled->isEmpty())
                        <p class="ss-muted">{{ text('admiro::dashboard.onboarding_dictionary_empty') }}</p>
                    @else
                        <ul class="ss-onboarding-languages__rows">
                            @foreach ($enabled as $entry)
                                @include('admiro::panel.onboarding.dictionary-row', ['prefix' => 'enabled'])
                            @endforeach
                        </ul>
                    @endif
                </section>
            @elseif ($current === \Simplysmart\Admiro\Panel\Enums\OnboardingStep::Administrators)
                @if ($term !== '')
                    <section class="ss-onboarding-picker__list" aria-labelledby="ss-admins-all-title">
                        <h3 class="ss-card__title" id="ss-admins-all-title">{{ text('admiro::dashboard.onboarding_admins_results', ['term' => $term]) }}</h3>
                        @if ($found === [])
                            <p class="ss-notice">{{ text('admiro::dashboard.onboarding_admins_none', ['term' => $term]) }}</p>
                        @else
                            <ul class="ss-onboarding-admins__rows">
                                @foreach ($found as $administrator)
                                    @include('admiro::panel.onboarding.administrator-row', ['prefix' => 'found-role'])
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endif

                <section class="ss-card ss-card--muted ss-onboarding-admins" aria-labelledby="ss-admins-profiles-title">
                    <h3 class="ss-card__title" id="ss-admins-profiles-title">{{ text('admiro::dashboard.onboarding_admins_profiles') }} <span class="ss-badge">{{ count($profiles) }}</span></h3>
                    <ul class="ss-onboarding-admins__profiles">
                        @foreach ($profiles as ['role' => $role, 'permissions' => $permissions, 'members' => $members])
                            <li class="ss-onboarding-admins__profile">
                                <x-admiro::icon :name="$role === 'guest' ? 'eye' : ($role === 'support' ? 'help-circle' : ($role === 'owner' ? 'shield-check' : 'rocket'))" class="ss-icon--tile" />
                                <span class="ss-onboarding-picker__chip-text">
                                    <span class="ss-onboarding-picker__name">{{ text('roles.admiro.' . $role) }}</span>
                                    <span class="ss-table__sub">{{ text('admiro::dashboard.onboarding_admins_profile_facts', ['permissions' => $permissions, 'members' => $members]) }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="ss-card ss-card--muted ss-onboarding-admins" aria-labelledby="ss-admins-title">
                    <h3 class="ss-card__title" id="ss-admins-title">{{ text('admiro::dashboard.onboarding_admins_title') }} <span class="ss-badge">{{ count($administrators) }}</span></h3>
                    @error('role')
                        <p class="ss-field__error" role="alert">{{ $message }}</p>
                    @enderror
                    <ul class="ss-onboarding-admins__rows">
                        @foreach ($administrators as $administrator)
                            @include('admiro::panel.onboarding.administrator-row', ['prefix' => 'role'])
                        @endforeach
                    </ul>
                    <a class="ss-link ss-onboarding-admins__all" href="{{ route($zone . 'administrators.index') }}">{{ text('admiro::dashboard.onboarding_admins_all') }}</a>
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
