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

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;

/**
 * Zapis stanu logowania w kolumnach konta strefy (Database\ZoneTables).
 */
final class EloquentAccountStore implements AccountStore
{
    public function recordFailedPin(Model&CodeAndPinAccount $account): int
    {
        $attempts = $account->failedPinAttempts() + 1;
        $account->forceFill([AccountColumns::PIN_FAILED_ATTEMPTS => $attempts])->save();

        return $attempts;
    }

    public function lock(Model&CodeAndPinAccount $account, CarbonInterface $until): void
    {
        $account->forceFill([
            AccountColumns::LOCKED_UNTIL => $until,
            AccountColumns::LOCK_LEVEL => $account->lockLevel() + 1,
        ])->save();
    }

    public function setPinHash(Model&CodeAndPinAccount $account, string $hash): void
    {
        $account->forceFill([AccountColumns::PIN_HASH => $hash, AccountColumns::PIN_SET_AT => Carbon::now()])->save();
    }

    public function recordSignIn(Model&CodeAndPinAccount $account, ?string $ip): void
    {
        $account->forceFill([
            AccountColumns::PIN_FAILED_ATTEMPTS => 0,
            AccountColumns::LOCK_LEVEL => 0,
            AccountColumns::LOCKED_UNTIL => null,
            AccountColumns::LAST_LOGIN_AT => Carbon::now(),
            AccountColumns::LAST_LOGIN_IP => $ip,
        ])->save();
    }

    public function bumpAuthEpoch(Model&CodeAndPinAccount $account): void
    {
        $account->forceFill([AccountColumns::AUTH_EPOCH => $account->authEpoch() + 1])->save();
    }
}
