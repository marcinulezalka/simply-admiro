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

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Auth\Events\SignInSucceeded;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Zones\ZoneRedirects;

/**
 * Wszystkie kroki wykonane: logowanie w guardzie strefy, nowa sesja z epoką konta, zapis
 * udanego logowania (zeruje błędy i blokady) i powrót na zamierzoną stronę albo dashboard.
 */
final readonly class CompleteSignIn
{
    public const string EPOCH_SESSION_KEY = 'auth_epoch';

    public function __construct(
        private AuthManager $auth,
        private AccountStore $store,
        private SignInFlowState $state,
        private PendingSignInResolver $pending,
        private CurrentZone $zone,
        private Dispatcher $events,
        private Redirector $redirect,
    ) {}

    public function handle(PendingSignIn $signIn, Request $request): RedirectResponse
    {
        $account = $signIn->account ?? $this->pending->abort();
        $zone = $this->zone->get();

        $guard = $this->auth->guard($zone->guard);

        if (! $guard instanceof StatefulGuard) {
            throw new \LogicException("Zone [{$zone->name}] guard must be a session guard.");
        }

        $guard->login($account);
        $request->session()->regenerate();
        $request->session()->put(self::EPOCH_SESSION_KEY, $account->authEpoch());
        $this->store->recordSignIn($account, $request->ip());
        $this->state->clear();

        $this->events->dispatch(new SignInSucceeded($zone->name, $account, $request->ip()));

        return $this->redirect->intended($this->redirect->getUrlGenerator()->route($zone->routeName(ZoneRedirects::HOME_ROUTE)));
    }
}
