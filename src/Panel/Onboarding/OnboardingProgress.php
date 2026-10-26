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

use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;

/**
 * Postęp onboardingu strefy administratorów: zapisane kamienie milowe (OnboardingStep::milestones()) w
 * `json_attributes` organizacji operatora (klucz ATTRIBUTE) — wspólne dla wszystkich administratorów i trwałe (nie
 * zależy od retencji dziennika zdarzeń). Bez organizacji operatora nic nie jest zrobione ani zapisane.
 */
final class OnboardingProgress
{
    public const string ATTRIBUTE = 'onboarding';

    public function mark(string $milestone): void
    {
        $operator = Organization::query()->find(Organization::OPERATOR_ID);

        if ($operator === null || in_array($milestone, $this->milestonesOf($operator), true)) {
            return;
        }

        $attributes = $operator->json_attributes ?? [];
        $attributes[self::ATTRIBUTE] = [...$this->milestonesOf($operator), $milestone];
        $operator->forceFill(['json_attributes' => $attributes])->save();
    }

    /**
     * Kroki w podanej kolejności z numerem (pozycja na liście, od 1) i stanem.
     *
     * @param list<OnboardingStep> $steps
     * @return list<array{step: OnboardingStep, number: int, done: bool}>
     */
    public function checklist(array $steps): array
    {
        $operator = Organization::query()->find(Organization::OPERATOR_ID);
        $milestones = $operator === null ? [] : $this->milestonesOf($operator);
        $checklist = [];

        foreach ($steps as $index => $step) {
            $checklist[] = ['step' => $step, 'number' => $index + 1, 'done' => array_diff($step->milestones(), $milestones) === []];
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
}
