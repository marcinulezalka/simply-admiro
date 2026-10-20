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

namespace Simplysmart\Mainboard\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Auth\SignIn\CompleteSignIn;
use Simplysmart\Mainboard\Auth\SignIn\SignOut;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\RevocableAccess;
use Simplysmart\Mainboard\Contracts\SignInAccount;
use Simplysmart\Mainboard\Zones\ZoneRedirects;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wylogowuje otwartą sesję konta, które nie może się już logować (dezaktywacja, blokada)
 * albo którego epoka się zmieniła (unieważnienie wszystkich sesji).
 */
final readonly class EnsureAccountCanSignIn
{
    public function __construct(
        private AuthManager $auth,
        private CurrentZone $zone,
        private SignOut $signOut,
        private Redirector $redirect,
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $zone = $this->zone->get();
        $account = $this->auth->guard($zone->guard)->user();

        if (! $account instanceof SignInAccount || $this->isValid($account, $request)) {
            return $next($request);
        }

        $this->signOut->handle($request);

        return $this->redirect->route($zone->routeName(ZoneRedirects::LOGIN_ROUTE));
    }

    private function isValid(SignInAccount $account, Request $request): bool
    {
        $epochMatches = ! $account instanceof RevocableAccess
            || $request->session()->get(CompleteSignIn::EPOCH_SESSION_KEY) === $account->authEpoch();

        return $account->canSignIn() && $epochMatches;
    }
}
