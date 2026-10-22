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
use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Auth\Events\AccountAccessRevoked;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Unieważnia dostęp do konta: nowa epoka (każda otwarta sesja zostanie wylogowana), usunięcie zapisanych
 * sesji i zdarzenie AccountAccessRevoked (Core odwołuje na nie zaufane urządzenia i zapisuje dziennik).
 */
final readonly class RevokeAccountAccess
{
    public function __construct(
        private AccountStore $store,
        private ZoneSessions $sessions,
        private Dispatcher $events,
    ) {}

    public function handle(ZoneDefinition $zone, Model&CodeAndPinAccount $account, AccessRevocationReason $reason, ?AccountActor $actor = null): void
    {
        $id = $account->getAuthIdentifier();

        $this->store->bumpAuthEpoch($account);

        if (is_int($id) || is_string($id)) {
            $this->sessions->forgetAccount($zone, $id);
        }

        $this->events->dispatch(new AccountAccessRevoked($zone->name, $account, $reason, $actor));
    }
}
