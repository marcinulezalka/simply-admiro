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

    public function show(Request $request, string $step, DictionaryList $dictionaries, DictionarySearch $search, InstallationGuide $guide, OrganizationList $organizations, OrganizationAssignments $assignments, OrganizationBadges $badges, AdmiroNavigation $navigation, RegionalSettingsSummary $regional, LocaleRouting $routing, ZoneAccounts $accounts, ZoneRoleNames $roleNames): View
    {
        $steps = $this->steps();
        $current = $this->step($step, $steps);
        $data = [
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
