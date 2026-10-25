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

namespace Simplysmart\Admiro\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Auth\SignIn\ResendSignInCode;
use Simplysmart\Admiro\Auth\SignIn\Steps\EmailCodeStep;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Http\SignInRoutes;

/**
 * Nowy kod logowania na kroku kodu (po odstępie między wysyłkami) z neutralnym komunikatem.
 */
final readonly class ResendCodeController
{
    public function __invoke(Request $request, ResendSignInCode $resend, CurrentZone $zone, Redirector $redirect): RedirectResponse
    {
        $resend->handle($request);

        return $redirect->route($zone->get()->routeName(SignInRoutes::stepRoute(EmailCodeStep::KEY)))
            ->with('status', text('sign_in.code_resent'));
    }
}
