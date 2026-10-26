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
use Simplysmart\Admiro\Panel\Enums\OnboardingStep;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Panel\Onboarding\OnboardingProgress;

/**
 * Pulpit administratora: kroki onboardingu (konfiguracja systemu w kolejności), które może wykonać, ze stanem z
 * OnboardingProgress, i odnośniki do sekcji, do których ma uprawnienia.
 */
final readonly class DashboardController
{
    public function __invoke(AdminContext $context, OnboardingProgress $progress): View
    {
        $steps = array_values(array_filter(OnboardingStep::cases(), static fn(OnboardingStep $step): bool => $context->can($step->permission())));

        return view('admiro::panel.dashboard', [
            'onboarding' => $progress->checklist($steps),
            'canViewZones' => $context->can(Permission::ZonesView),
            'canViewOrganizations' => $context->can(Permission::OrganizationsView),
            'canAssignOwners' => $context->can(Permission::OwnersAssign),
            'canManageAdministrators' => $context->can(Permission::UsersManage),
            'canViewAudit' => $context->can(Permission::AuditView),
            'canViewSettings' => $context->can(Permission::SettingsView),
        ]);
    }
}
