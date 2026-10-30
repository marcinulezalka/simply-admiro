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

namespace Simplysmart\Admiro\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Contracts\CurrentAccount;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\OrganizationSelector;
use Simplysmart\Admiro\Permissions\RoleSelector;
use Simplysmart\Admiro\Queries\Organizations\ActiveOrganizations;
use Simplysmart\Admiro\Zones\ZoneContexts;
use Simplysmart\Admiro\Zones\ZoneRedirects;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Przełączenie kontekstu jednym kliknięciem (Zones\ZoneContexts): organizacja, w której konto jest członkiem,
 * i — w strefie z `role_contexts` — rola konta w niej; potem dashboard strefy z uprawnieniami tego kontekstu.
 * Organizacja spoza członkostw albo cudza rola — 404.
 */
final readonly class ContextSwitchController
{
    private const int MAX_LENGTH = 512;

    public function __invoke(Request $request, CurrentZone $zone, CurrentAccount $current, OrganizationSelector $organizations, RoleSelector $roles, ActiveOrganizations $active, ZoneRedirects $redirects): RedirectResponse
    {
        $validated = $request->validate([ZoneContexts::FIELD => ['required', 'string', 'max:' . self::MAX_LENGTH]]);
        $value = $validated[ZoneContexts::FIELD];
        $account = $current->get();
        [$token, $role] = is_string($value) ? array_pad(explode(ZoneContexts::SEPARATOR, $value, 2), 2, '') : ['', ''];
        $organization = $token !== '' && strlen($token) <= Organization::TOKEN_LENGTH ? $active->find($token) : null;
        $definition = $zone->get();

        if (! $account instanceof ZoneAccount || $organization === null || ! $organizations->choose($account, $organization, $request->session())) {
            throw new NotFoundHttpException();
        }

        if ($role !== '' && (! $definition->roleContexts || ! $roles->choose($definition, $account, $organization->id, $role, $request->session()))) {
            throw new NotFoundHttpException();
        }

        $request->session()->regenerate();

        return new RedirectResponse($redirects->homeFor($definition, $account, $request->session()));
    }
}
