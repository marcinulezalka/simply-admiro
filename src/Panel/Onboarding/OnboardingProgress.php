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

namespace Simplysmart\Admiro\Panel\Onboarding;

use Carbon\CarbonImmutable;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Enums\OnboardingStepState;

/**
 * Postęp onboardingu strefy administratorów: zapisane kamienie milowe (OnboardingStep::milestones()) w
 * `json_attributes` organizacji operatora (klucz ATTRIBUTE) — wspólne dla wszystkich administratorów i trwałe (nie
 * zależy od retencji dziennika zdarzeń), a obok (klucz DATES_ATTRIBUTE) chwila zapisu każdego kamienia milowego.
 * Zadanie zrobione ma datę zakończenia: najpóźniejszy z jego kamieni milowych, a instalacja (bez kamieni) — założenie
 * organizacji operatora; kamienie zapisane, zanim powstały daty, daty nie mają. Bez organizacji operatora nic nie jest
 * zrobione ani zapisane.
 */
final class OnboardingProgress
{
    public const string ATTRIBUTE = 'onboarding';

    public const string DATES_ATTRIBUTE = 'onboarding_completed_at';

    public function mark(string $milestone): void
    {
        $operator = Organization::query()->find(Organization::OPERATOR_ID);

        if ($operator === null || in_array($milestone, $this->milestonesOf($operator), true)) {
            return;
        }

        $attributes = $operator->json_attributes ?? [];
        $attributes[self::ATTRIBUTE] = [...$this->milestonesOf($operator), $milestone];
        $attributes[self::DATES_ATTRIBUTE] = [...$this->datesOf($operator), $milestone => CarbonImmutable::now()->toIso8601String()];
        $operator->forceFill(['json_attributes' => $attributes])->save();
    }

    /**
     * Zadania w podanej kolejności z numerem (pozycja na liście, od 1), stanem, skrótem done (stan Done) i datą
     * zakończenia (tylko zrobione, gdy jest znana).
     *
     * @param list<OnboardingStep> $steps
     * @return list<array{step: OnboardingStep, number: int, state: OnboardingStepState, done: bool, completedAt: CarbonImmutable|null}>
     */
    public function checklist(array $steps): array
    {
        $operator = Organization::query()->find(Organization::OPERATOR_ID);
        $milestones = $operator === null ? [] : $this->milestonesOf($operator);
        $dates = $operator === null ? [] : $this->datesOf($operator);
        $checklist = [];

        foreach ($steps as $index => $step) {
            $state = OnboardingStepState::of($step->milestones(), $milestones);
            $done = $state === OnboardingStepState::Done;
            $checklist[] = ['step' => $step, 'number' => $index + 1, 'state' => $state, 'done' => $done, 'completedAt' => $done && $operator !== null ? $this->completedAt($step, $operator, $dates) : null];
        }

        return $checklist;
    }

    /**
     * @return list<string>
     */
    private function milestonesOf(Organization $operator): array
    {
        $milestones = ($operator->json_attributes ?? [])[self::ATTRIBUTE] ?? [];

        return is_array($milestones) ? array_values(array_filter($milestones, is_string(...))) : [];
    }

    /**
     * @param array<string, string> $dates
     */
    private function completedAt(OnboardingStep $step, Organization $operator, array $dates): ?CarbonImmutable
    {
        $required = $step->milestones();

        if ($required === []) {
            return $operator->created_at === null ? null : CarbonImmutable::instance($operator->created_at);
        }

        $known = array_intersect_key($dates, array_flip($required));

        return count($known) === count($required) ? CarbonImmutable::parse(max($known)) : null;
    }

    /**
     * @return array<string, string>
     */
    private function datesOf(Organization $operator): array
    {
        $dates = ($operator->json_attributes ?? [])[self::DATES_ATTRIBUTE] ?? [];

        return is_array($dates) ? array_filter($dates, static fn(mixed $date, int|string $milestone): bool => is_string($milestone) && is_string($date), ARRAY_FILTER_USE_BOTH) : [];
    }
}
