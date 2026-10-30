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

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Simplysmart\Admiro\Auth\PinLength;
use Simplysmart\Admiro\Auth\PinRules;
use Simplysmart\Admiro\Auth\ScreenLock;
use Simplysmart\Admiro\Auth\SignIn\PinCheckResult;
use Simplysmart\Admiro\Auth\SignIn\PinVerifier;
use Simplysmart\Admiro\Auth\SignIn\SignOut;
use Simplysmart\Admiro\Contracts\CurrentAccount;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Zones\ZoneRedirects;

/**
 * Blokada ekranu (jak Win+L, Auth\ScreenLock): zablokowanie bez wylogowania, ekran PIN-u i odblokowanie poprawnym
 * PIN-em (Auth\SignIn\PinVerifier: błędne próby liczą się jak przy logowaniu, a po ich limicie konto jest blokowane
 * i wylogowane). Po odblokowaniu powrót na stronę sprzed blokady, a bez niej na pulpit kontekstu.
 */
final readonly class ScreenLockController
{
    public function __construct(
        private ScreenLock $lock,
        private CurrentZone $zone,
        private CurrentAccount $account,
    ) {}

    public function lock(Request $request): RedirectResponse
    {
        $this->lock->lock($request->session(), $request->headers->get('referer'));

        return new RedirectResponse(route($this->zone->get()->routeName(AccountRoutes::UNLOCK)));
    }

    public function show(): View
    {
        return view('admiro::account.unlock', [
            'name' => $this->account->require()->displayName(),
            'pinLength' => PinLength::accepted(),
        ]);
    }

    public function unlock(Request $request, PinVerifier $pins, SignOut $signOut, ZoneRedirects $redirects): RedirectResponse
    {
        $validated = $request->validate(['pin' => PinRules::forEnteredPin()]);
        $account = $this->account->require();

        return match ($pins->verify($account, is_string($validated['pin']) ? $validated['pin'] : '', $request->ip())) {
            PinCheckResult::Valid => $this->unlocked($request, $redirects),
            PinCheckResult::Invalid => throw ValidationException::withMessages(['pin' => text('account.screen_lock.invalid')]),
            PinCheckResult::Locked => $this->signedOut($request, $signOut),
        };
    }

    private function unlocked(Request $request, ZoneRedirects $redirects): RedirectResponse
    {
        $returnTo = $this->lock->unlock($request->session());
        $request->session()->regenerate();

        return new RedirectResponse($returnTo ?? $redirects->homeFor($this->zone->get(), $this->account->require(), $request->session()));
    }

    private function signedOut(Request $request, SignOut $signOut): RedirectResponse
    {
        $signOut->handle($request);

        return (new RedirectResponse(route($this->zone->get()->routeName(ZoneRedirects::LOGIN_ROUTE))))->with('status', text('account.screen_lock.locked_out'));
    }
}
