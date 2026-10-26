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

namespace Simplysmart\Admiro\Audit\Listeners;

use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Audit\AuditEntries;
use Simplysmart\Admiro\Audit\Events\AdminActionPerformed;
use Simplysmart\Admiro\Audit\Events\RecordAccessed;
use Simplysmart\Admiro\Audit\Events\RecordStatusChanged;
use Simplysmart\Admiro\Auth\Events\AccountAccessRevoked;
use Simplysmart\Admiro\Auth\Events\AccountLocked;
use Simplysmart\Admiro\Auth\Events\PinChanged;
use Simplysmart\Admiro\Auth\Events\RecoveryEmailChanged;
use Simplysmart\Admiro\Auth\Events\SignedOut;
use Simplysmart\Admiro\Auth\Events\SignInRejected;
use Simplysmart\Admiro\Auth\Events\SignInSucceeded;
use Simplysmart\Admiro\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Admiro\Auth\Events\TrustedDevicesRevoked;
use Simplysmart\Admiro\Models\AuditEvent;
use Simplysmart\Admiro\Permissions\Events\ZoneRoleChanged;

/**
 * Dziennik zdarzeń w tabeli `audit_events` (podgląd w panelu administratora, filtry po strefie i dacie).
 */
final readonly class StoreAuditEvent
{
    public function __construct(
        private AuditEntries $entries,
    ) {}

    public function handle(
        SignInSucceeded|SignInRejected|AccountLocked|SignedOut|AccountAccessRevoked|PinChanged|RecoveryEmailChanged|TrustedDeviceRegistered|TrustedDevicesRevoked|ZoneRoleChanged|AdminActionPerformed|RecordAccessed|RecordStatusChanged $event,
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
