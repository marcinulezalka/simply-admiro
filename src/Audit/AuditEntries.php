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

namespace Simplysmart\Mainboard\Audit;

use Simplysmart\Mainboard\Audit\Events\AdminActionPerformed;
use Simplysmart\Mainboard\Auth\Events\AccountAccessRevoked;
use Simplysmart\Mainboard\Auth\Events\AccountLocked;
use Simplysmart\Mainboard\Auth\Events\PinChanged;
use Simplysmart\Mainboard\Auth\Events\SignedOut;
use Simplysmart\Mainboard\Auth\Events\SignInRejected;
use Simplysmart\Mainboard\Auth\Events\SignInSucceeded;
use Simplysmart\Mainboard\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Mainboard\Auth\Events\TrustedDevicesRevoked;
use Simplysmart\Mainboard\Contracts\SignInAccount;
use Simplysmart\Mainboard\Permissions\Events\ZoneRoleChanged;

/**
 * Jedyne miejsce, które zamienia zdarzenia Core na wpisy dziennika (plik `mainboard.auth.log_channel`
 * i tabela `audit_events`), więc oba dzienniki mówią to samo.
 */
final class AuditEntries
{
    /**
     * Zdarzenia zapisywane w dzienniku.
     *
     * @var list<class-string>
     */
    public const array EVENTS = [
        SignInSucceeded::class,
        SignInRejected::class,
        AccountLocked::class,
        SignedOut::class,
        AccountAccessRevoked::class,
        PinChanged::class,
        TrustedDeviceRegistered::class,
        TrustedDevicesRevoked::class,
        ZoneRoleChanged::class,
        AdminActionPerformed::class,
    ];

    public function from(
        SignInSucceeded|SignInRejected|AccountLocked|SignedOut|AccountAccessRevoked|PinChanged|TrustedDeviceRegistered|TrustedDevicesRevoked|ZoneRoleChanged|AdminActionPerformed $event,
    ): AuditEntry {
        return match (true) {
            $event instanceof SignInSucceeded => new AuditEntry('sign-in', AuditLevel::Info, $event->zone, $this->id($event->account), ip: $event->ip),
            $event instanceof SignInRejected => new AuditEntry('pin rejected', AuditLevel::Notice, $event->zone, $this->id($event->account), ip: $event->ip, context: ['failed_attempts' => $event->failedAttempts]),
            $event instanceof AccountLocked => new AuditEntry('account locked', AuditLevel::Warning, $event->zone, $this->id($event->account), context: ['until' => $event->until->toIso8601String()]),
            $event instanceof SignedOut => new AuditEntry('sign-out', AuditLevel::Info, $event->zone, $event->accountId),
            $event instanceof AccountAccessRevoked => new AuditEntry('access revoked', AuditLevel::Notice, $event->zone, $this->id($event->account), $event->actor, context: ['reason' => $event->reason->value]),
            $event instanceof PinChanged => new AuditEntry('pin changed', AuditLevel::Info, $event->zone, $this->id($event->account), ip: $event->ip),
            $event instanceof TrustedDeviceRegistered => new AuditEntry('trusted device registered', AuditLevel::Info, $event->zone, $this->id($event->account), ip: $event->ip, context: ['device' => $event->deviceName]),
            $event instanceof TrustedDevicesRevoked => new AuditEntry('trusted devices revoked', AuditLevel::Info, $event->zone, $event->accountId, context: ['count' => $event->count, 'cause' => $event->cause]),
            $event instanceof ZoneRoleChanged => new AuditEntry($event->assigned ? 'role assigned' : 'role revoked', AuditLevel::Notice, $event->zone, $event->accountId, $event->actor, $event->organizationId, context: ['role' => $event->role]),
            $event instanceof AdminActionPerformed => new AuditEntry($event->action, AuditLevel::Notice, $event->zone, $event->accountId, $event->actor, $event->organizationId, context: $event->context),
        };
    }

    private function id(SignInAccount $account): int|string|null
    {
        $id = $account->getAuthIdentifier();

        return is_int($id) || is_string($id) ? $id : null;
    }
}
