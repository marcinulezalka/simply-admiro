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

namespace Simplysmart\Mainboard\Audit\Listeners;

use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Audit\AuditEntries;
use Simplysmart\Mainboard\Audit\Events\AdminActionPerformed;
use Simplysmart\Mainboard\Audit\Events\RecordAccessed;
use Simplysmart\Mainboard\Auth\Events\AccountAccessRevoked;
use Simplysmart\Mainboard\Auth\Events\AccountLocked;
use Simplysmart\Mainboard\Auth\Events\PinChanged;
use Simplysmart\Mainboard\Auth\Events\SignedOut;
use Simplysmart\Mainboard\Auth\Events\SignInRejected;
use Simplysmart\Mainboard\Auth\Events\SignInSucceeded;
use Simplysmart\Mainboard\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Mainboard\Auth\Events\TrustedDevicesRevoked;
use Simplysmart\Mainboard\Models\AuditEvent;
use Simplysmart\Mainboard\Permissions\Events\ZoneRoleChanged;

/**
 * Dziennik zdarzeń w tabeli `audit_events` (podgląd w panelu administratora, filtry po strefie i dacie).
 */
final readonly class StoreAuditEvent
{
    public function __construct(
        private AuditEntries $entries,
    ) {}

    public function handle(
        SignInSucceeded|SignInRejected|AccountLocked|SignedOut|AccountAccessRevoked|PinChanged|TrustedDeviceRegistered|TrustedDevicesRevoked|ZoneRoleChanged|AdminActionPerformed|RecordAccessed $event,
    ): void {
        $entry = $this->entries->from($event);

        (new AuditEvent())->forceFill([
            'occurred_at' => Carbon::now(),
            'event' => $entry->event,
            'level' => $entry->level->value,
            'zone' => $entry->zone,
            'account_id' => $entry->accountId === null ? null : (string) $entry->accountId,
            'actor_zone' => $entry->actor?->zone,
            'actor_id' => $entry->actor === null ? null : (string) $entry->actor->accountId,
            'organization_id' => $entry->organizationId,
            'ip' => $entry->ip,
            'context' => $entry->context === [] ? null : $entry->context,
        ])->save();
    }
}
