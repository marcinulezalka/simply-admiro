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

namespace Simplysmart\Mainboard\Http\Controllers\Account;

use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Auth\ChangePin;
use Simplysmart\Mainboard\Auth\SignIn\IssueSignInCode;
use Simplysmart\Mainboard\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Mainboard\Auth\SignInLimit;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Http\AccountRoutes;
use Simplysmart\Mainboard\Rules\StrongPin;

/**
 * Zmiana PIN-u zalogowanego konta: formularz, wysłanie kodu na adres konta i zapis nowego PIN-u.
 */
final readonly class PinChangeController
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $current,
        private Redirector $redirect,
    ) {}

    public function show(): View
    {
        $settings = $this->zone->get()->auth();

        return view('mainboard::account.pin', ['pinLength' => $settings->pinLength, 'codeLength' => $settings->codeLength]);
    }

    public function sendCode(Request $request, LoginAttemptLimiter $limiter, Dispatcher $bus): RedirectResponse
    {
        $account = $this->account();

        $limiter->attempt(SignInLimit::AccountCodeRequests, $account->emailAddress(), 'code');
        $bus->dispatch(new IssueSignInCode($this->zone->get()->name, $account->emailAddress(), $request->ip(), $request->userAgent()));

        return $this->back()->with('status', text('account.code_sent'));
    }

    public function update(Request $request, ChangePin $change): RedirectResponse
    {
        $settings = $this->zone->get()->auth();
        $validated = $request->validate([
            'current_pin' => ['required', 'string', 'digits:' . $settings->pinLength],
            'code' => ['required', 'string', 'digits:' . $settings->codeLength],
            'pin' => ['required', 'string', 'digits:' . $settings->pinLength, 'confirmed', new StrongPin()],
        ]);

        $change->handle(
            $this->account(),
            is_string($validated['current_pin']) ? $validated['current_pin'] : '',
            is_string($validated['code']) ? $validated['code'] : '',
            is_string($validated['pin']) ? $validated['pin'] : '',
            $request,
        );

        return $this->back()->with('status', text('account.pin_changed'));
    }

    private function account(): ZoneAccount
    {
        return $this->current->require();
    }

    private function back(): RedirectResponse
    {
        return $this->redirect->route($this->zone->get()->routeName(AccountRoutes::PIN));
    }
}
