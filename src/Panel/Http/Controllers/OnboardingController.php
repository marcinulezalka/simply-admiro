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
use Simplysmart\Admiro\Localization\Dictionary;
use Simplysmart\Admiro\Models\Dictionaries\Language;
use Simplysmart\Admiro\Models\Organization;
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
    public function __construct(
        private AdminContext $context,
        private OnboardingProgress $progress,
        private Redirector $redirect,
    ) {}

    public function index(): View
    {
        return view('admiro::panel.onboarding.index', ['onboarding' => $this->progress->checklist($this->steps())]);
    }

    public function show(Request $request, string $step, DictionaryList $dictionaries, DictionarySearch $search, InstallationGuide $guide, OrganizationList $organizations, AdmiroNavigation $navigation, RegionalSettingsSummary $regional): View
    {
        $steps = $this->steps();
        $current = $this->step($step, $steps);
        $data = [
            'checklist' => $this->progress->checklist($steps),
            'current' => $current,
            'previous' => $current->previous($steps),
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
            $data += [
                'term' => $term,
                'enabled' => $all->filter(static fn(Language $language): bool => $language->is_active === true)->sortBy($defaultFirst)->values(),
                'languages' => $term === '' ? new Collection() : $search->languages($term)->sortBy($defaultFirst)->values(),
                'canManage' => $this->context->can(Permission::SettingsManage),
                'regional' => $regional->summary(),
                'regionalConfigured' => collect($data['checklist'])->firstWhere('step', OnboardingStep::Settings)['done'] ?? false,
            ];
        }

        if ($current === OnboardingStep::Organizations) {
            $term = $request->string('q')->trim()->toString();
            $data += [
                'term' => $term,
                'enabled' => $organizations->all()->filter(static fn(Organization $organization): bool => $organization->is_active === true)->values(),
                'organizations' => $term === '' ? new Collection() : $organizations->search($term, AdmiroSearch::PAGE),
                'canManage' => $this->context->can(Permission::OrganizationsManage),
                'organizationActions' => $navigation->organizationActions(),
            ];
        }

        return view('admiro::panel.onboarding.show', $data);
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
        return array_values(array_filter(OnboardingStep::cases(), fn(OnboardingStep $step): bool => $this->context->can($step->permission())));
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
