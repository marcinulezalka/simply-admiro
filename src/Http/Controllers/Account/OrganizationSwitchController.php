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

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Permissions\OrganizationSelector;
use Simplysmart\Mainboard\Queries\Organizations\ActiveOrganizations;
use Simplysmart\Mainboard\Zones\ZoneRedirects;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Przełącza konto na inną swoją organizację (po tokenie); organizacja spoza członkostw konta to 404.
 */
final readonly class OrganizationSwitchController
{
    public const string FIELD = 'organization';

    public function __invoke(Request $request, CurrentZone $zone, AuthFactory $auth, OrganizationSelector $selector, ActiveOrganizations $organizations, Redirector $redirect): RedirectResponse
    {
        $validated = $request->validate([self::FIELD => ['required', 'string', 'max:' . Organization::TOKEN_LENGTH]]);
        $account = $auth->guard($zone->get()->guard)->user();
        $organization = is_string($validated[self::FIELD]) ? $organizations->find($validated[self::FIELD]) : null;

        if (! $account instanceof Model || $organization === null || ! $selector->choose($account, $organization, $request->session())) {
            throw new NotFoundHttpException();
        }

        $request->session()->regenerate();

        return $redirect->route($zone->get()->routeName(ZoneRedirects::HOME_ROUTE));
    }
}
