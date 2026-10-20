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

namespace Simplysmart\Mainboard\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Auth\SignIn\SignOut;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Zones\ZoneRedirects;

final readonly class LogoutController
{
    public function __invoke(Request $request, SignOut $signOut, CurrentZone $zone, Redirector $redirect): RedirectResponse
    {
        $signOut->handle($request);

        return $redirect->route($zone->get()->routeName(ZoneRedirects::LOGIN_ROUTE));
    }
}
