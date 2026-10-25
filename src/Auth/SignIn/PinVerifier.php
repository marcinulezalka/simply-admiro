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

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Auth\Events\AccountLocked;
use Simplysmart\Mainboard\Auth\Events\SignInRejected;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Sprawdza PIN konta: przy poprawnym aktualizuje przestarzały hash, przy błędnym liczy próby
 * i blokuje konto na coraz dłużej (licznik i poziom blokady zeruje dopiero udane logowanie).
 */
final readonly class PinVerifier
{
    public function __construct(
        private Hasher $hasher,
        private AccountStore $store,
        private Dispatcher $events,
        private CurrentZone $zone,
    ) {}

    public function verify(Model&CodeAndPinAccount $account, string $pin, ?string $ip): PinCheckResult
    {
        if ($account->isLocked()) {
            return PinCheckResult::Locked;
        }

        $hash = $account->pinHash();

        if ($hash !== null && $this->hasher->check($pin, $hash)) {
            if ($this->hasher->needsRehash($hash)) {
                $this->store->setPinHash($account, $this->hasher->make($pin));
            }

            return PinCheckResult::Valid;
        }

        $zone = $this->zone->get();
        $failed = $this->store->recordFailedPin($account);
        $this->events->dispatch(new SignInRejected($zone->name, $account, $failed, $ip));

        if ($failed < $zone->auth()->pinMaxAttempts) {
            return PinCheckResult::Invalid;
        }

        $until = Carbon::now()->addMinutes($zone->auth()->lockoutMinutesFor($account->lockLevel()));
        $this->store->lock($account, $until);
        $this->events->dispatch(new AccountLocked($zone->name, $account, $until));

        return PinCheckResult::Locked;
    }
}
