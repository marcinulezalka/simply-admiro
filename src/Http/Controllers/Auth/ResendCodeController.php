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
use Simplysmart\Mainboard\Auth\SignIn\ResendSignInCode;
use Simplysmart\Mainboard\Auth\SignIn\Steps\EmailCodeStep;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Http\SignInRoutes;

final readonly class ResendCodeController
{
    public function __invoke(Request $request, ResendSignInCode $resend, CurrentZone $zone, Redirector $redirect): RedirectResponse
    {
        $resend->handle($request);

        return $redirect->route($zone->get()->routeName(SignInRoutes::stepRoute(EmailCodeStep::KEY)))
            ->with('status', text('sign_in.code_resent'));
    }
}
