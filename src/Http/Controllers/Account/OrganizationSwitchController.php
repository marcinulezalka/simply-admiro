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

use Simplysmart\Admiro\Contracts\CurrentAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Permissions\OrganizationSelector;
use Simplysmart\Admiro\Queries\Organizations\ActiveOrganizations;
use Simplysmart\Admiro\Zones\ZoneRedirects;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Przełącza konto na inną swoją organizację (po tokenie); organizacja spoza członkostw konta to 404.
 */
final readonly class OrganizationSwitchController
{
    public const string FIELD = 'organization';

    public function __invoke(Request $request, CurrentZone $zone, CurrentAccount $current, OrganizationSelector $selector, ActiveOrganizations $organizations, ZoneRedirects $redirects): RedirectResponse
    {
        $validated = $request->validate([self::FIELD => ['required', 'string', 'max:' . Organization::TOKEN_LENGTH]]);
        $account = $current->get();
        $organization = is_string($validated[self::FIELD]) ? $organizations->find($validated[self::FIELD]) : null;

        if (! $account instanceof Model || $organization === null || ! $selector->choose($account, $organization, $request->session())) {
            throw new NotFoundHttpException();
        }

        $request->session()->regenerate();

        return new RedirectResponse($redirects->homeFor($zone->get(), $account, $request->session()));
    }
}
