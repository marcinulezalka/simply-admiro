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

use Illuminate\Config\Repository;
use Illuminate\Log\LogManager;
use Simplysmart\Mainboard\Audit\AuditEntries;
use Simplysmart\Mainboard\Audit\Events\AdminActionPerformed;
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
 * Dziennik zdarzeń w pliku (kanał `mainboard.auth.log_channel`).
 */
final readonly class WriteAuditLog
{
    public function __construct(
        private AuditEntries $entries,
        private LogManager $logs,
        private Repository $config,
    ) {}

    public function handle(
        SignInSucceeded|SignInRejected|AccountLocked|SignedOut|AccountAccessRevoked|PinChanged|TrustedDeviceRegistered|TrustedDevicesRevoked|ZoneRoleChanged|AdminActionPerformed $event,
    ): void {
        $entry = $this->entries->from($event);

        $this->logs->channel($this->config->string('mainboard.auth.log_channel'))->log($entry->level->value, $entry->event, $entry->toLogContext());
    }
}
