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

use Illuminate\Config\Repository;
use Illuminate\Log\LogManager;
use Simplysmart\Mainboard\Auth\Events\AccountAccessRevoked;
use Simplysmart\Mainboard\Auth\Events\AccountLocked;
use Simplysmart\Mainboard\Auth\Events\PinChanged;
use Simplysmart\Mainboard\Auth\Events\SignedOut;
use Simplysmart\Mainboard\Auth\Events\SignInRejected;
use Simplysmart\Mainboard\Auth\Events\SignInSucceeded;
use Simplysmart\Mainboard\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Mainboard\Auth\Events\TrustedDevicesRevoked;
use Simplysmart\Mainboard\Permissions\Events\ZoneRoleChanged;

/**
 * Dziennik logowań (kanał `mainboard.auth.log_channel`): strefa i identyfikator konta, bez adresu e-mail;
 * przy akcjach administracyjnych także aktor.
 */
final readonly class LogSignInActivity
{
    public function __construct(
        private LogManager $logs,
        private Repository $config,
    ) {}

    public function handle(
        SignInSucceeded|SignInRejected|AccountLocked|SignedOut|AccountAccessRevoked|PinChanged|TrustedDeviceRegistered|TrustedDevicesRevoked|ZoneRoleChanged $event,
    ): void {
        $log = $this->logs->channel($this->config->string('mainboard.auth.log_channel'));

        match (true) {
            $event instanceof SignInSucceeded => $log->info('sign-in', ['zone' => $event->zone, 'account_id' => $event->account->getAuthIdentifier(), 'ip' => $event->ip]),
            $event instanceof SignInRejected => $log->notice('pin rejected', ['zone' => $event->zone, 'account_id' => $event->account->getAuthIdentifier(), 'failed_attempts' => $event->failedAttempts, 'ip' => $event->ip]),
            $event instanceof AccountLocked => $log->warning('account locked', ['zone' => $event->zone, 'account_id' => $event->account->getAuthIdentifier(), 'until' => $event->until->toIso8601String()]),
            $event instanceof SignedOut => $log->info('sign-out', ['zone' => $event->zone, 'account_id' => $event->accountId]),
            $event instanceof AccountAccessRevoked => $log->notice('access revoked', ['zone' => $event->zone, 'account_id' => $event->account->getAuthIdentifier(), 'reason' => $event->reason->value, 'actor' => $event->actor?->toLogContext()]),
            $event instanceof PinChanged => $log->info('pin changed', ['zone' => $event->zone, 'account_id' => $event->account->getAuthIdentifier(), 'ip' => $event->ip]),
            $event instanceof TrustedDeviceRegistered => $log->info('trusted device registered', ['zone' => $event->zone, 'account_id' => $event->account->getAuthIdentifier(), 'device' => $event->deviceName, 'ip' => $event->ip]),
            $event instanceof ZoneRoleChanged => $log->notice($event->assigned ? 'role assigned' : 'role revoked', ['zone' => $event->zone, 'account_id' => $event->accountId, 'role' => $event->role, 'organization_id' => $event->organizationId, 'actor' => $event->actor->toLogContext()]),
            $event instanceof TrustedDevicesRevoked => $log->info('trusted devices revoked', ['zone' => $event->zone, 'account_id' => $event->accountId, 'count' => $event->count, 'cause' => $event->cause]),
        };
    }
}
