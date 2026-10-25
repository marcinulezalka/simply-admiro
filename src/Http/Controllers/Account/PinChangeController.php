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

use Simplysmart\Admiro\Contracts\CurrentAccount;
use Simplysmart\Admiro\Models\ZoneAccount;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Auth\ChangePin;
use Simplysmart\Admiro\Auth\PinRules;
use Simplysmart\Admiro\Auth\SignIn\IssueSignInCode;
use Simplysmart\Admiro\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Admiro\Auth\SignInLimit;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Http\AccountRoutes;

/**
 * Zmiana PIN-u zalogowanego konta (formularz na stronie bezpieczeństwa, SecurityController): wysłanie kodu na adres
 * konta i zapis nowego PIN-u.
 */
final readonly class PinChangeController
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $current,
        private Redirector $redirect,
    ) {}

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
            'current_pin' => PinRules::forEnteredPin(),
            'code' => ['required', 'string', 'digits:' . $settings->codeLength],
            'pin' => PinRules::forNewPin($settings->pinLength),
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
        return SecurityController::redirectTo($this->redirect, $this->zone, AccountRoutes::SECTION_PIN);
    }
}
