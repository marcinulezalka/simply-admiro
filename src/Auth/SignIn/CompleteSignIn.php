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

namespace Simplysmart\Admiro\Auth\SignIn;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Auth\Devices\RegisterTrustedDevice;
use Simplysmart\Admiro\Auth\Events\SignInSucceeded;
use Simplysmart\Admiro\Auth\SignIn\Steps\EmailCodeStep;
use Simplysmart\Admiro\Auth\SignIn\Steps\PinSetupStep;
use Simplysmart\Admiro\Contracts\AccountStore;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Localization\LocaleRouting;
use Simplysmart\Admiro\Profile\AccountLocale;
use Simplysmart\Admiro\Zones\ZoneRedirects;

/**
 * Wszystkie kroki wykonane: logowanie w guardzie strefy, nowa sesja z epoką konta, zapis
 * udanego logowania (zeruje błędy i blokady), zapamiętanie urządzenia, jeśli konto o to poprosiło,
 * i powrót na zamierzoną stronę albo dashboard (w języku wybranym w profilu konta, Profile\AccountLocale). Pierwsze
 * logowanie (z ustawieniem PIN-u) zawsze kończy się na dashboardzie strefy: zamierzona strona sprzed logowania jest
 * pomijana, bo nowe konto zaczyna od pulpitu swojej roli, a nie od strony, którą ktoś otworzył przed nim.
 */
final readonly class CompleteSignIn
{
    public const string EPOCH_SESSION_KEY = 'auth_epoch';

    /** Klucz sesji Laravela z zamierzoną stroną (Redirector::intended()). */
    private const string INTENDED_SESSION_KEY = 'url.intended';

    public function __construct(
        private AuthManager $auth,
        private AccountStore $store,
        private SignInFlowState $state,
        private PendingSignInResolver $pending,
        private CurrentZone $zone,
        private Dispatcher $events,
        private Redirector $redirect,
        private RegisterTrustedDevice $devices,
        private AccountLocale $locale,
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

        if ($zone->auth()->trustedDevicesEnabled && $signIn->wantsDeviceRemembered(EmailCodeStep::KEY)) {
            $this->devices->handle($account, $request);
        }

        $this->state->clear();

        $this->events->dispatch(new SignInSucceeded($zone->name, $account, $request->ip()));

        $locale = $this->locale->of($account);

        $home = $this->redirect->getUrlGenerator()->route($zone->routeName(ZoneRedirects::HOME_ROUTE), $locale !== null ? [LocaleRouting::PARAMETER => $locale] : []);

        if ($signIn->hasCompleted(PinSetupStep::KEY)) {
            $request->session()->forget(self::INTENDED_SESSION_KEY);

            return $this->redirect->to($home);
        }

        return $this->redirect->intended($home);
    }
}
