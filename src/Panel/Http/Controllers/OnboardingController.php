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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Localization\Dictionary;
use Simplysmart\Admiro\Models\Dictionaries\Language;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Panel\Onboarding\OnboardingProgress;
use Simplysmart\Admiro\Queries\Dictionaries\DictionaryList;
use Simplysmart\Admiro\Queries\Dictionaries\DictionarySearch;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Kreator onboardingu (nagłówek „Onboarding”, kroki z OnboardingStep, które administrator może wykonać): wejście
 * prowadzi do pierwszego niezrobionego kroku; krok „Języki” to lista języków z przełącznikami (włączone osobno nad
 * listą, filtr `q` jak w globalnej wyszukiwarce — także po krajach) zatwierdzana przyciskiem, pozostałe kroki opisują
 * zadanie i prowadzą do jego strony panelu. Krok spoza uprawnień albo nieznany to 404.
 */
final readonly class OnboardingController
{
    public function __construct(
        private AdminContext $context,
        private OnboardingProgress $progress,
        private Redirector $redirect,
    ) {}

    public function index(): RedirectResponse
    {
        $checklist = $this->progress->checklist($this->steps());

        if ($checklist === []) {
            return $this->redirect->route($this->context->zone()->routeName('dashboard'));
        }

        $pending = array_values(array_filter($checklist, static fn(array $item): bool => ! $item['done']));

        return $this->redirect->route($this->context->zone()->routeName('onboarding.show'), ['step' => ($pending[0] ?? $checklist[0])['step']->value]);
    }

    public function show(Request $request, string $step, DictionaryList $dictionaries, DictionarySearch $search): View
    {
        $steps = $this->steps();
        $current = $this->step($step, $steps);
        $data = [
            'checklist' => $this->progress->checklist($steps),
            'current' => $current,
            'previous' => $current->previous($steps),
            'next' => $current->next($steps),
        ];

        if ($current === OnboardingStep::Languages) {
            $term = $request->string('q')->trim()->toString();
            /** @var \Illuminate\Database\Eloquent\Collection<int, Language> $all */
            $all = $dictionaries->entries(Dictionary::Languages);
            $data += [
                'term' => $term,
                'enabled' => $all->filter(static fn(Language $language): bool => $language->is_active === true)->sortBy(static fn(Language $language): string => $language->translatedName())->values(),
                'languages' => $term === '' ? $all->sortBy(static fn(Language $language): string => $language->translatedName())->values() : $search->languages($term),
                'canManage' => $this->context->can(Permission::SettingsManage),
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

        $audit->record('onboarding step confirmed', context: ['step' => $current->value]);
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
