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

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Auth\Recovery\RecoveryPinReset;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Http\SignInRoutes;
use Simplysmart\Mainboard\Zones\ZoneRedirects;

/**
 * „Nie pamiętam PIN-u” (gość): jak ustawić nowy PIN, a z zapasowym adresem e-mail — reset samodzielnie
 * (Auth\Recovery\RecoveryPinReset): adres konta → kod na zapasowy adres → PIN usunięty, powrót do logowania.
 */
final readonly class ForgotPinController
{
    public function __construct(
        private CurrentZone $zone,
        private Redirector $redirect,
    ) {}

    public function show(Request $request, RecoveryPinReset $reset): View
    {
        return view('mainboard::auth.forgot-pin', ['codePending' => $reset->isPending($request), 'codeLength' => $this->zone->get()->auth()->codeLength]);
    }

    public function sendCode(Request $request, RecoveryPinReset $reset): RedirectResponse
    {
        $validated = $request->validate([RecoveryPinReset::EMAIL_FIELD => ['required', 'string', 'email:rfc', 'max:255']]);
        $email = $validated[RecoveryPinReset::EMAIL_FIELD];
        $reset->sendCode(is_string($email) ? $email : '', $request);

        return $this->back()->with('status', text('sign_in.forgot_pin.recovery.code_sent'));
    }

    public function reset(Request $request, RecoveryPinReset $reset): RedirectResponse
    {
        $validated = $request->validate([RecoveryPinReset::CODE_FIELD => ['required', 'string', 'digits:' . $this->zone->get()->auth()->codeLength]]);
        $code = $validated[RecoveryPinReset::CODE_FIELD];
        $reset->confirm(is_string($code) ? $code : '', $request);

        return $this->redirect->route($this->zone->get()->routeName(ZoneRedirects::LOGIN_ROUTE))->with('status', text('sign_in.forgot_pin.recovery.done'));
    }

    public function cancel(Request $request, RecoveryPinReset $reset): RedirectResponse
    {
        $reset->cancel($request);

        return $this->back();
    }

    private function back(): RedirectResponse
    {
        return $this->redirect->route($this->zone->get()->routeName('auth.' . SignInRoutes::FORGOT_PIN));
    }
}
