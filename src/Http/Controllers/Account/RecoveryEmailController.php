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

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Mainboard\Auth\Recovery\RecoveryEmailRegistration;
use Simplysmart\Mainboard\Contracts\CurrentAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Http\AccountRoutes;

/**
 * Zapasowy adres e-mail zalogowanego konta (sekcja `recovery` strony bezpieczeństwa,
 * Auth\Recovery\RecoveryEmailRegistration): wysłanie kodu na nowy adres, potwierdzenie kodem i usunięcie adresu.
 */
final readonly class RecoveryEmailController
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $account,
        private Redirector $redirect,
    ) {}

    public function sendCode(Request $request, RecoveryEmailRegistration $registration): RedirectResponse
    {
        $validated = $request->validate([RecoveryEmailRegistration::EMAIL_FIELD => ['required', 'string', 'email:rfc', 'max:255']]);
        $email = $validated[RecoveryEmailRegistration::EMAIL_FIELD];
        $registration->sendCode($this->account->require(), is_string($email) ? $email : '', $request);

        return $this->back()->with('status', text('account.recovery.code_sent'));
    }

    public function update(Request $request, RecoveryEmailRegistration $registration): RedirectResponse
    {
        $validated = $request->validate([RecoveryEmailRegistration::CODE_FIELD => ['required', 'string', 'digits:' . $this->zone->get()->auth()->codeLength]]);
        $code = $validated[RecoveryEmailRegistration::CODE_FIELD];
        $registration->confirm($this->account->require(), is_string($code) ? $code : '', $request);

        return $this->back()->with('status', text('account.recovery.saved'));
    }

    public function destroy(Request $request, RecoveryEmailRegistration $registration): RedirectResponse
    {
        $registration->remove($this->account->require(), $request);

        return $this->back()->with('status', text('account.recovery.removed'));
    }

    private function back(): RedirectResponse
    {
        return SecurityController::redirectTo($this->redirect, $this->zone, AccountRoutes::SECTION_RECOVERY);
    }
}
