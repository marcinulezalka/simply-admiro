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

namespace Simplysmart\Admiro\Auth;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Simplysmart\Admiro\Auth\Events\PinChanged;
use Simplysmart\Admiro\Auth\SignIn\CodeCheckResult;
use Simplysmart\Admiro\Auth\SignIn\CompleteSignIn;
use Simplysmart\Admiro\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Admiro\Auth\SignIn\LoginChallengeService;
use Simplysmart\Admiro\Auth\SignIn\PinCheckResult;
use Simplysmart\Admiro\Auth\SignIn\PinVerifier;
use Simplysmart\Admiro\Contracts\AccountStore;
use Simplysmart\Admiro\Contracts\CodeAndPinAccount;
use Simplysmart\Admiro\Contracts\CurrentZone;

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
        $this->limiter->attempt(SignInLimit::PinChanges, (string) AccountKey::of($account), 'current_pin');

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
