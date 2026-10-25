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

namespace Simplysmart\Admiro\Http\Middleware;

use Simplysmart\Admiro\Contracts\CurrentAccount;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Auth\SignIn\CompleteSignIn;
use Simplysmart\Admiro\Auth\SignIn\SignOut;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\RevocableAccess;
use Simplysmart\Admiro\Contracts\SignInAccount;
use Simplysmart\Admiro\Zones\ZoneRedirects;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wylogowuje otwartą sesję konta, które nie może się już logować (dezaktywacja, blokada)
 * albo którego epoka się zmieniła (unieważnienie wszystkich sesji).
 */
final readonly class EnsureAccountCanSignIn
{
    public function __construct(
        private CurrentAccount $account,
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
        $account = $this->account->get();

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
