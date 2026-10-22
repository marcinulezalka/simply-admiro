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

namespace Simplysmart\Mainboard\Auth;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Simplysmart\Mainboard\Auth\Events\PinChanged;
use Simplysmart\Mainboard\Auth\SignIn\CodeCheckResult;
use Simplysmart\Mainboard\Auth\SignIn\CompleteSignIn;
use Simplysmart\Mainboard\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Mainboard\Auth\SignIn\LoginChallengeService;
use Simplysmart\Mainboard\Auth\SignIn\PinCheckResult;
use Simplysmart\Mainboard\Auth\SignIn\PinVerifier;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Zmiana PIN-u zalogowanego konta: wymaga obecnego PIN-u (liczonego do blokady jak przy logowaniu) i kodu
 * z e-maila, a nowy PIN musi być inny. Potem unieważnia pozostałe sesje i zaufane urządzenia konta;
 * bieżąca sesja zostaje (nowa epoka, nowy identyfikator).
 */
final readonly class ChangePin
{
    public function __construct(
        private LoginAttemptLimiter $limiter,
        private PinVerifier $pins,
        private LoginChallengeService $challenges,
        private Hasher $hasher,
        private AccountStore $store,
        private RevokeAccountAccess $revoke,
        private CurrentZone $zone,
        private Dispatcher $events,
    ) {}

    /**
     * @throws ValidationException Gdy limit jest wyczerpany, PIN lub kod jest błędny albo nowy PIN jest taki sam.
     */
    public function handle(Model&CodeAndPinAccount $account, string $currentPin, string $code, string $newPin, Request $request): void
    {
        $zone = $this->zone->get();
        $id = $account->getAuthIdentifier();

        $this->limiter->attempt(SignInLimit::PinChanges, is_int($id) || is_string($id) ? (string) $id : '', 'current_pin');

        if ($this->pins->verify($account, $currentPin, $request->ip()) !== PinCheckResult::Valid) {
            throw ValidationException::withMessages(['current_pin' => text('account.pin_invalid')]);
        }

        if ($this->challenges->verify($account, $code) !== CodeCheckResult::Valid) {
            throw ValidationException::withMessages(['code' => text('account.code_invalid')]);
        }

        $hash = $account->pinHash();

        if ($hash !== null && $this->hasher->check($newPin, $hash)) {
            throw ValidationException::withMessages(['pin' => text('account.pin_same')]);
        }

        $this->store->setPinHash($account, $this->hasher->make($newPin));
        $this->revoke->handle($zone, $account, AccessRevocationReason::PinChanged);

        $request->session()->regenerate();
        $request->session()->put(CompleteSignIn::EPOCH_SESSION_KEY, $account->authEpoch());

        $this->events->dispatch(new PinChanged($zone->name, $account, $request->ip()));
    }
}
