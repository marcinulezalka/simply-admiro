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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Http\SignInRoutes;

/**
 * Po każdym kroku: przekierowanie na następny wymagany krok albo zakończenie logowania.
 */
final readonly class SignInFlowNavigator
{
    public function __construct(
        private SignInFlow $flow,
        private PendingSignInResolver $pending,
        private CompleteSignIn $completion,
        private CurrentZone $zone,
        private Redirector $redirect,
    ) {}

    public function continue(Request $request): RedirectResponse
    {
        $zone = $this->zone->get();
        $signIn = $this->pending->resolve();
        $next = $this->flow->next($zone->auth(), $signIn);

        return $next === null
            ? $this->completion->handle($signIn, $request)
            : $this->redirect->route($zone->routeName(SignInRoutes::stepRoute($next->key())));
    }
}
