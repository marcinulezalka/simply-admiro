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

use Simplysmart\Admiro\Audit\Events\AdminActionPerformed;
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Permissions\Events\ZoneRoleChanged;

/**
 * Oznacza kamienie milowe onboardingu po akcjach administratora w panelu (krok „Języki” zamyka zatwierdzenie
 * w kreatorze — `onboarding step confirmed` — nie zmiana słownika): zapis ustawień, publikacja
 * polityki cookies i dokumentów prawnych, nowa organizacja, nowy właściciel strefy oraz nadanie roli w strefie
 * administratorów (ZoneRoleChanged — rola z konsoli przez SystemRoleGrant się nie liczy).
 */
final readonly class RecordOnboardingProgress
{
    public function __construct(
        private OnboardingProgress $progress,
    ) {}

    public function handle(AdminActionPerformed|ZoneRoleChanged $event): void
    {
        $milestone = $event instanceof ZoneRoleChanged ? $this->roleMilestone($event) : $this->actionMilestone($event);

        if ($milestone !== null) {
            $this->progress->mark($milestone);
        }
    }

    private function actionMilestone(AdminActionPerformed $event): ?string
    {
        $document = $event->context['document'] ?? null;

        return match ($event->action) {
            'onboarding step confirmed' => $event->context['step'] === OnboardingStep::Languages->value ? OnboardingStep::Languages->value : null,
            'settings updated' => OnboardingStep::Settings->value,
            'cookie policy published' => OnboardingStep::legalMilestone('cookies'),
            'legal document published' => is_string($document) ? OnboardingStep::legalMilestone($document) : null,
            'organization created' => OnboardingStep::Organizations->value,
            'owner provisioned' => OnboardingStep::Owners->value,
            default => null,
        };
    }

    private function roleMilestone(ZoneRoleChanged $event): ?string
    {
        return $event->assigned && $event->zone === config()->string('admiro.zone.name') ? OnboardingStep::Administrators->value : null;
    }
}
