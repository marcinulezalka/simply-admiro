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
 * w kreatorze — OnboardingStep::CONFIRMED_ACTION — nie zmiana słownika): zapis ustawień, publikacja
 * polityki cookies i dokumentów prawnych (zapis ustawień zamyka też „Kraje”, bo ustawia kraj domyślny — także z kreatora
 * „Język i tłumaczenia”), nowa organizacja, nowy właściciel strefy oraz nadanie roli w strefie
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

        if ($event instanceof AdminActionPerformed && $event->action === 'settings updated') {
            $this->progress->mark(OnboardingStep::Countries->value);
        }
    }

    private function actionMilestone(AdminActionPerformed $event): ?string
    {
        $document = $event->context['document'] ?? null;

        return match ($event->action) {
            OnboardingStep::CONFIRMED_ACTION => $event->context['step'] === OnboardingStep::Languages->value ? OnboardingStep::Languages->value : null,
            'settings updated' => OnboardingStep::Settings->value,
            'dictionary entry enabled', 'dictionary entry disabled' => match ($event->context['dictionary'] ?? null) {
                'languages' => OnboardingStep::Languages->value,
                'countries' => OnboardingStep::Countries->value,
                'currencies' => OnboardingStep::Currencies->value,
                default => null,
            },
            'exchange rates synced', 'exchange rate added' => OnboardingStep::Currencies->value,
            'holidays synced', 'holiday added' => OnboardingStep::Calendar->value,
            'tax rate added', 'tax rate ended' => OnboardingStep::Taxes->value,
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
