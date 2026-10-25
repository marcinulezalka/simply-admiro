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

use Illuminate\Config\Repository;
use Illuminate\Log\LogManager;
use Simplysmart\Admiro\Audit\AuditEntries;
use Simplysmart\Admiro\Audit\Events\AdminActionPerformed;
use Simplysmart\Admiro\Audit\Events\RecordAccessed;
use Simplysmart\Admiro\Auth\Events\AccountAccessRevoked;
use Simplysmart\Admiro\Auth\Events\AccountLocked;
use Simplysmart\Admiro\Auth\Events\PinChanged;
use Simplysmart\Admiro\Auth\Events\RecoveryEmailChanged;
use Simplysmart\Admiro\Auth\Events\SignedOut;
use Simplysmart\Admiro\Auth\Events\SignInRejected;
use Simplysmart\Admiro\Auth\Events\SignInSucceeded;
use Simplysmart\Admiro\Auth\Events\TrustedDeviceRegistered;
use Simplysmart\Admiro\Auth\Events\TrustedDevicesRevoked;
use Simplysmart\Admiro\Permissions\Events\ZoneRoleChanged;

/**
 * Dziennik zdarzeń w pliku (kanał `admiro.auth.log_channel`).
 */
final readonly class WriteAuditLog
{
    public function __construct(
        private AuditEntries $entries,
        private LogManager $logs,
        private Repository $config,
    ) {}

    public function handle(
        SignInSucceeded|SignInRejected|AccountLocked|SignedOut|AccountAccessRevoked|PinChanged|RecoveryEmailChanged|TrustedDeviceRegistered|TrustedDevicesRevoked|ZoneRoleChanged|AdminActionPerformed|RecordAccessed $event,
    ): void {
        $entry = $this->entries->from($event);

        $this->logs->channel($this->config->string('admiro.auth.log_channel'))->log($entry->level->value, $entry->event, $entry->toLogContext());
    }
}
