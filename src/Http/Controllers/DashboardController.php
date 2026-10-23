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

namespace Simplysmart\Admiro\Http\Controllers;

use Illuminate\Contracts\View\View;
use Simplysmart\Admiro\Enums\Permission;
use Simplysmart\Admiro\Http\AdminContext;

/**
 * Pulpit administratora: odnośniki do sekcji, do których ma uprawnienia.
 */
final readonly class DashboardController
{
    public function __invoke(AdminContext $context): View
    {
        return view('admiro::dashboard', [
            'canViewZones' => $context->can(Permission::ZonesView),
            'canViewOrganizations' => $context->can(Permission::OrganizationsView),
            'canAssignOwners' => $context->can(Permission::OwnersAssign),
            'canManageAdministrators' => $context->can(Permission::UsersManage),
        ]);
    }
}
