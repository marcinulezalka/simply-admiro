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

namespace Simplysmart\Mainboard\Auth\Listeners;

use Illuminate\Contracts\Events\Dispatcher;
use Simplysmart\Mainboard\Auth\Events\AccountAccessRevoked;
use Simplysmart\Mainboard\Auth\Events\AccountLocked;
use Simplysmart\Mainboard\Auth\Events\TrustedDevicesRevoked;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\TrustedDeviceRepository;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;

/**
 * Konto bez zaufanych urządzeń po unieważnieniu dostępu (zmiana i reset PIN-u, odblokowanie, dezaktywacja,
 * skopiowany token) i po blokadzie za błędne PIN-y. Działa w strefie konta, nie żądania.
 */
final readonly class RevokeTrustedDevicesOfAccount
{
    public const string CAUSE_LOCKED = 'account_locked';

    public function __construct(
        private ZoneRegistry $zones,
        private CurrentZone $current,
        private TrustedDeviceRepository $devices,
        private Dispatcher $events,
    ) {}

    public function handle(AccountAccessRevoked|AccountLocked $event): void
    {
        $accountId = $event->account->getAuthIdentifier();

        if (! is_int($accountId) && ! is_string($accountId)) {
            return;
        }

        $count = $this->current->within($this->zones->get($event->zone), fn(): int => $this->devices->revokeAll($accountId));
        $cause = $event instanceof AccountAccessRevoked ? $event->reason->value : self::CAUSE_LOCKED;

        if ($count > 0) {
            $this->events->dispatch(new TrustedDevicesRevoked($event->zone, $accountId, $count, $cause));
        }
    }
}
