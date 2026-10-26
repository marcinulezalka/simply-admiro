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
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminContext;

/**
 * Status systemu (pulpit administratora): odnośniki do sekcji, do których administrator ma uprawnienia; przegląd kroków
 * onboardingu jest w OnboardingController::index.
 */
final readonly class DashboardController
{
    public function __invoke(AdminContext $context): View
    {
        return view('admiro::panel.dashboard', [
            'canViewZones' => $context->can(Permission::ZonesView),
            'canViewOrganizations' => $context->can(Permission::OrganizationsView),
            'canAssignOwners' => $context->can(Permission::OwnersAssign),
            'canManageAdministrators' => $context->can(Permission::UsersManage),
            'canViewAudit' => $context->can(Permission::AuditView),
            'canViewSettings' => $context->can(Permission::SettingsView),
        ]);
    }
}
