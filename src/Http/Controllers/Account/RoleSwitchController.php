<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Permissions\RoleSelector;
use Simplysmart\Mainboard\Zones\ZoneRedirects;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Przełącza konto na inną swoją rolę (kontekst) w bieżącej organizacji i przekierowuje na pulpit strefy, który
 * pokazuje widok tej roli; rola, której konto nie ma, to 404.
 */
final readonly class RoleSwitchController
{
    public const string FIELD = 'role';

    private const int ROLE_NAME_MAX_LENGTH = 255;

    public function __invoke(Request $request, CurrentZone $zone, CurrentAccount $current, CurrentOrganization $organization, RoleSelector $selector, Redirector $redirect): RedirectResponse
    {
        $validated = $request->validate([self::FIELD => ['required', 'string', 'max:' . self::ROLE_NAME_MAX_LENGTH]]);
        $account = $current->get();
        $organizationId = $organization->id();
        $role = $validated[self::FIELD];

        if (! $account instanceof ZoneAccount || $organizationId === null || ! is_string($role) || ! $selector->choose($zone->get(), $account, $organizationId, $role, $request->session())) {
            throw new NotFoundHttpException();
        }

        $request->session()->regenerate();

        return $redirect->route($zone->get()->routeName(ZoneRedirects::HOME_ROUTE));
    }
}
