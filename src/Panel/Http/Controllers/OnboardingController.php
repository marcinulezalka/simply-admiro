<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/admiro.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Admiro\Panel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Simplysmart\Admiro\Queries\Dictionaries\DefaultEntries;
use Simplysmart\Admiro\Translation\TranslationImport;
use Simplysmart\Admiro\Translation\TranslationTemplate;
use Simplysmart\Admiro\Auth\AccountKey;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Queries\Accounts\ZoneAccounts;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Admiro\Localization\LocaleRouting;
use Simplysmart\Admiro\Localization\LocalizationSettings;
use Simplysmart\Admiro\Localization\LocalizationSettingsRejected;
use Simplysmart\Admiro\Localization\Dictionary;
use Simplysmart\Admiro\Models\Dictionaries\Language;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Organizations\OrganizationBadges;
use Simplysmart\Admiro\Organizations\OrganizationTree;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Panel\Http\AdmiroNavigation;
use Simplysmart\Admiro\Panel\Onboarding\DictionarySteps;
use Simplysmart\Admiro\Panel\Onboarding\InstallationGuide;
use Simplysmart\Admiro\Panel\Onboarding\OnboardingProgress;
use Simplysmart\Admiro\Panel\Onboarding\RegionalSettingsSummary;
use Simplysmart\Admiro\Panel\Search\AdmiroSearch;
use Simplysmart\Admiro\Queries\Dictionaries\DictionaryList;
use Simplysmart\Admiro\Queries\Dictionaries\DictionarySearch;
use Simplysmart\Admiro\Queries\Organizations\OrganizationAssignments;
use Simplysmart\Admiro\Queries\Organizations\OrganizationList;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Kreator onboardingu (nagłówek „Onboarding”, kroki z OnboardingStep, które administrator może wykonać): wejście
 * pokazuje przegląd kroków z postępem (karty niezrobionych kroków); krok „Języki” to lista języków z przełącznikami (włączone osobno nad
 * listą — domyślny zawsze pierwszy, z ustawieniami regionalnymi RegionalSettingsSummary —, filtr `q` jak w globalnej wyszukiwarce — także po krajach) zatwierdzana przyciskiem; krok „Organizacje” na tym
 * samym wzorze: aktywne organizacje nad wyszukiwarką (`q`: nazwa, nazwa skrócona, NIP; także wyłączone) z menu „+”
 * zakładania organizacji, przełączniki zapisują się od razu, a krok zamyka założenie organizacji; pozostałe kroki opisują
 * zadanie i prowadzą do jego strony panelu. Krok spoza uprawnień albo nieznany to 404.
 */
final readonly class OnboardingController
{
    /**
     * Najwięcej administratorów w panelu zadania „Profile i uprawnienia” (pełna lista na stronie administratorów).
     */
    private const int ADMINISTRATORS = 100;

    public function __construct(
        private AdminContext $context,
        private OnboardingProgress $progress,
        private Redirector $redirect,
    ) {}

    public function index(): View
    {
        return view('admiro::panel.onboarding.index', ['onboarding' => $this->progress->checklist($this->steps())]);
    }

    public function show(Request $request, string $step, DictionaryList $dictionaries, DictionarySearch $search, InstallationGuide $guide, OrganizationList $organizations, OrganizationAssignments $assignments, OrganizationBadges $badges, AdmiroNavigation $navigation, RegionalSettingsSummary $regional, LocaleRouting $routing, ZoneAccounts $accounts, ZoneRoleNames $roleNames, DictionarySteps $dictionarySteps, TranslationTemplate $template, DefaultEntries $defaults): View
    {
        $steps = $this->steps();
        $current = $this->step($step, $steps);
        $data = [
            'wizardActions' => $navigation->wizardActions($current),
            'checklist' => $this->progress->checklist($steps),
            'current' => $current,
            'next' => $current->next($steps),
        ];

        if ($current === OnboardingStep::Installation) {
            $data['installationGuide'] = $guide->stages();
        }

        if ($current === OnboardingStep::Languages) {
            $term = $request->string('q')->trim()->toString();
            /** @var Collection<int, Language> $all */
            $all = $dictionaries->entries(Dictionary::Languages);
            $defaultFirst = static fn(Language $language): array => [$language->is_default === true ? 0 : 1, $language->translatedName()];
            $enabled = $all->filter(static fn(Language $language): bool => $language->is_active === true)->sortBy($defaultFirst)->values();
            $configured = collect($data['checklist'])->firstWhere('step', OnboardingStep::Settings)['done'] ?? false;
            $regionals = [];
            $source = $defaults->language()->code ?? config()->string('app.locale');

            foreach ($enabled as $language) {
                $regionals[$language->code] = $language->is_default === true && $configured ? $regional->summary() : $regional->forLanguage($language);
            }

            $data += [
                'term' => $term,
                'all' => $all,
                'enabled' => $enabled,
                'languages' => $term === '' ? new Collection() : $search->languages($term)->sortBy($defaultFirst)->values(),
                'canManage' => $this->context->can(Permission::SettingsManage),
                'regionals' => $regionals,
                'regionalConfigured' => $configured,
                'defaultCandidates' => $routing->enabled(),
                'keywords' => $enabled->mapWithKeys(static fn(Language $language): array => [$language->code => $template->counts($language->code, $source)])->all(),
            ];
        }

        if (in_array($current, [OnboardingStep::Countries, OnboardingStep::Currencies, OnboardingStep::Calendar, OnboardingStep::Taxes], true)) {
            $data += $dictionarySteps->data($current, $request->string('q')->trim()->toString()) + [
                'canManage' => $this->context->can(Permission::SettingsManage),
            ];
        }

        if ($current === OnboardingStep::Administrators) {
            $term = $request->string('q')->trim()->toString();
            $zone = $this->context->zone();
            $administrators = array_values(array_filter($accounts->page($zone, null, self::ADMINISTRATORS)->items(), static fn(mixed $account): bool => $account instanceof ZoneAccount));
            $found = $term === '' ? [] : array_values(array_filter($accounts->search($zone, $term, AdmiroSearch::PAGE), static fn(mixed $account): bool => $account instanceof ZoneAccount));
            $available = $roleNames->available($zone);
            $rolesOf = [];

            foreach ([...$administrators, ...$found] as $account) {
                $rolesOf[AccountKey::of($account)] ??= $roleNames->of($account, Organization::OPERATOR_ID);
            }

            $counts = $roleNames->permissionCounts($zone);
            $profiles = [];

            foreach ($available as $role) {
                $profiles[] = [
                    'role' => $role,
                    'permissions' => $counts[$role] ?? 0,
                    'members' => count(array_filter($administrators, static fn(ZoneAccount $account): bool => in_array($role, $rolesOf[AccountKey::of($account)] ?? [], true))),
                ];
            }

            $data += [
                'term' => $term,
                'administrators' => $administrators,
                'found' => $found,
                'rolesOf' => $rolesOf,
                'availableRoles' => $available,
                'profiles' => $profiles,
                'self' => $this->context->admin(),
                'canManage' => $this->context->can(Permission::UsersManage),
            ];
        }

        if ($current === OnboardingStep::Organizations) {
            $term = $request->string('q')->trim()->toString();
            $all = $organizations->all();
            $ids = array_values($all->map(static fn(Organization $organization): int => $organization->id)->all());
            $data += [
                'term' => $term,
                'all' => $all,
                'activeCount' => $all->filter(static fn(Organization $organization): bool => $organization->is_active === true)->count(),
                'tree' => OrganizationTree::of($all),
                'assignments' => $assignments->of($ids),
                'badges' => $badges->of($ids),
                'canAssignOwners' => $this->context->can(Permission::OwnersAssign),
                'canViewZones' => $this->context->can(Permission::ZonesView),
                'organizations' => $term === '' ? new Collection() : $organizations->search($term, AdmiroSearch::PAGE),
                'canManage' => $this->context->can(Permission::OrganizationsManage),
                'organizationActions' => $navigation->organizationActions(),
            ];
        }

        return view('admiro::panel.onboarding.show', $data);
    }

    /**
     * „Zapisz jako domyślne” w zadaniu „Języki”: język (`language`, jeden z APP_LOCALES) staje się domyślnym językiem
     * aplikacji i interfejsu, a kraj i waluta — proponowanymi dla niego (RegionalSettingsSummary::forLanguage());
     * zapis jak w ustawieniach (LocalizationSettings, dziennik `settings updated` zamyka zadanie „Ustawienia
     * regionalne”), a zadanie „Języki” jest potwierdzone. Bez kraju lub waluty w propozycji albo przy odmowie — błąd.
     */
    public function saveRegional(Request $request, RegionalSettingsSummary $regional, LocalizationSettings $settings, LocaleRouting $routing, AdminAudit $audit): RedirectResponse
    {
        $validated = $request->validate(['language' => ['required', 'string', Rule::in($routing->enabled())]]);
        $language = Language::query()->where(Language::CODE, $validated['language'])->first() ?? throw new NotFoundHttpException();
        ['country' => $country, 'currency' => $currency] = $regional->forLanguage($language);
        $back = $this->redirect->route($this->context->zone()->routeName('onboarding.show'), ['step' => OnboardingStep::Languages->value]);

        if ($country === null || $currency === null) {
            return $back->withErrors(['regional' => text('admiro::dashboard.onboarding_regional_unknown')]);
        }

        $interface = Language::query()->where('is_interface', true)->pluck(Language::CODE)->all();
        $interface = array_values(array_intersect([...array_filter($interface, is_string(...)), $language->code], $routing->enabled()));

        try {
            $settings->update($country, $language, $currency, $interface);
        } catch (LocalizationSettingsRejected) {
            return $back->withErrors(['regional' => text('admiro::dashboard.onboarding_regional_unknown')]);
        }

        $audit->record('settings updated', context: ['country' => $country->alpha_code_2, 'language' => $language->code, 'currency' => $currency->alpha_code_3]);
        $audit->record(OnboardingStep::CONFIRMED_ACTION, context: ['step' => OnboardingStep::Languages->value]);

        return $back->with('status', text('admiro::dashboard.onboarding_regional_saved'));
    }

    /**
     * Szablon tłumaczeń włączonego języka jako plik JSON (Translation\TranslationTemplate): teksty Core i paczek w języku
     * domyślnym aplikacji z istniejącymi tłumaczeniami na ten język; pobranie trafia do dziennika.
     */
    public function translations(string $code, TranslationTemplate $template, DefaultEntries $defaults, AdminAudit $audit): Response
    {
        $language = Language::query()->where(Language::CODE, $code)->where('is_active', true)->first() ?? throw new NotFoundHttpException();
        $source = $defaults->language()->code ?? config()->string('app.locale');
        $audit->record('translation template downloaded', context: ['language' => $language->code]);

        return new JsonResponse($template->for($language->code, $source), 200, [
            'Content-Disposition' => 'attachment; filename="translations-' . $language->code . '.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Eksport (ikona przy „+” w zadaniu „Języki”): jeden plik JSON z szablonami tłumaczeń wszystkich włączonych języków
     * (`languages`: lista dokumentów w formacie pojedynczego szablonu), który import przyjmuje bez zmian.
     */
    public function exportTranslations(TranslationTemplate $template, DefaultEntries $defaults, AdminAudit $audit): Response
    {
        $source = $defaults->language()->code ?? config()->string('app.locale');
        $languages = Language::query()->where('is_active', true)->orderBy(Language::CODE)->pluck(Language::CODE)->all();
        $audit->record('translations exported', context: ['languages' => count($languages)]);

        return new JsonResponse([
            'source' => $source,
            'languages' => array_map(static fn(string $code): array => $template->for($code, $source), array_values(array_filter($languages, is_string(...)))),
        ], 200, [
            'Content-Disposition' => 'attachment; filename="translations.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Import plików tłumaczeń JSON z sekcji „Tłumaczenia” zadania „Języki” (format szablonu, Translation\TranslationImport,
     * albo eksport wszystkich języków z listą `languages`): każdy dokument wskazuje włączony język (`language`), a jego niepuste tłumaczenia trafiają do nadpisań aplikacji
     * `lang/vendor/`; import trafia do dziennika. Plik bez poprawnego JSON-u albo z nieznanym językiem — błąd, bez zapisu.
     */
    public function importTranslations(Request $request, TranslationImport $import, DefaultEntries $defaults, AdminAudit $audit): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'max:' . TranslationImport::MAX_FILES],
            'files.*' => ['required', 'file', 'extensions:json', 'max:' . TranslationImport::MAX_KILOBYTES],
        ]);
        $source = $defaults->language()->code ?? config()->string('app.locale');
        $back = $this->redirect->to(route($this->context->zone()->routeName('onboarding.show'), ['step' => OnboardingStep::Languages->value]) . '#' . OnboardingStep::TRANSLATIONS_ANCHOR);
        $documents = [];

        $files = $request->file('files');

        foreach (is_array($files) ? $files : [] as $file) {
            $decoded = json_decode((string) file_get_contents($file->getRealPath()), true);
            $parts = is_array($decoded) && is_array($decoded['languages'] ?? null) ? $decoded['languages'] : [$decoded];

            foreach ($parts as $document) {
                $language = is_array($document) && is_string($document['language'] ?? null)
                    ? Language::query()->where(Language::CODE, $document['language'])->where('is_active', true)->first()
                    : null;

                if ($language === null || ! is_array($document['texts'] ?? null)) {
                    return $back->withErrors(['translations' => text('admiro::dashboard.onboarding_translations_invalid', ['file' => $file->getClientOriginalName()])]);
                }

                $documents[] = ['language' => $language->code, 'texts' => $document['texts']];
            }
        }

        $count = 0;

        foreach ($documents as $document) {
            $imported = $import->import($document['language'], $source, $document['texts']);
            $audit->record('translations imported', context: ['language' => $document['language'], 'texts' => $imported]);
            $count += $imported;
        }

        return $back->with('status', text('admiro::dashboard.onboarding_translations_imported', ['count' => local_number($count)]));
    }

    public function confirm(string $step, AdminAudit $audit): RedirectResponse
    {
        $steps = $this->steps();
        $current = $this->step($step, $steps);

        if ($current !== OnboardingStep::Languages) {
            throw new NotFoundHttpException();
        }

        $audit->record(OnboardingStep::CONFIRMED_ACTION, context: ['step' => $current->value]);
        $next = $current->next($steps);
        $zone = $this->context->zone();
        $target = $next === null
            ? $this->redirect->route($zone->routeName('dashboard'))
            : $this->redirect->route($zone->routeName('onboarding.show'), ['step' => $next->value]);

        return $target->with('status', text('admiro::dashboard.onboarding_confirmed', ['name' => text('admiro::dashboard.onboarding_' . $current->value . '_title')]));
    }

    /**
     * @return list<OnboardingStep>
     */
    private function steps(): array
    {
        return OnboardingStep::permittedFor($this->context);
    }

    /**
     * @param list<OnboardingStep> $steps
     *
     * @throws NotFoundHttpException Gdy krok jest nieznany albo poza uprawnieniami.
     */
    private function step(string $value, array $steps): OnboardingStep
    {
        $step = OnboardingStep::tryFrom($value);

        return $step !== null && in_array($step, $steps, true) ? $step : throw new NotFoundHttpException();
    }
}
