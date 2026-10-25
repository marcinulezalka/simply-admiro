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

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Contracts\AccountStore;
use Simplysmart\Admiro\Contracts\CodeAndPinAccount;
use Simplysmart\Admiro\Zones\AccountZone;

/**
 * Akcje administratora na koncie strefy (bez UI; wywołuje je panel albo komenda strefy). Każda unieważnia
 * sesje i zaufane urządzenia konta, a dziennik `sign_in` zapisuje powód i aktora (AccountAccessRevoked).
 *
 * Konto może należeć do innej strefy niż żądanie (np. administrator z innej strefy): akcja działa w strefie
 * konta (Zones\AccountZone).
 */
final readonly class AccountAdministration
{
    public function __construct(
        private AccountStore $store,
        private RevokeAccountAccess $revoke,
        private AccountZone $zones,
    ) {}

    /**
     * Usuwa PIN; właściciel ustawi nowy po kodzie z e-maila przy najbliższym logowaniu.
     */
    public function resetPin(Model&CodeAndPinAccount $account, ?AccountActor $actor = null): void
    {
        $zone = $this->zones->of($account);
        $this->store->clearPin($account);
        $this->revoke->handle($zone, $account, AccessRevocationReason::PinReset, $actor);
    }

    /**
     * Zdejmuje blokadę po błędnych PIN-ach.
     */
    public function unlock(Model&CodeAndPinAccount $account, ?AccountActor $actor = null): void
    {
        $zone = $this->zones->of($account);
        $this->store->unlock($account);
        $this->revoke->handle($zone, $account, AccessRevocationReason::Unlocked, $actor);
    }

    /**
     * Wyłącza konto: logowanie przechodzi ścieżką decoy, otwarte sesje są wylogowywane.
     */
    public function deactivate(Model&CodeAndPinAccount $account, ?AccountActor $actor = null): void
    {
        $zone = $this->zones->of($account);
        $this->store->deactivate($account);
        $this->revoke->handle($zone, $account, AccessRevocationReason::Deactivated, $actor);
    }
}
