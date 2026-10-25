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

namespace Simplysmart\Mainboard\Auth\Events;

use Simplysmart\Mainboard\Auth\AccessRevocationReason;
use Simplysmart\Mainboard\Auth\AccountActor;
use Simplysmart\Mainboard\Contracts\SignInAccount;

/**
 * Wszystkie sesje konta zostały unieważnione (nowa epoka); Core odwołuje też jego zaufane urządzenia.
 */
final readonly class AccountAccessRevoked
{
    public function __construct(
        public string $zone,
        public SignInAccount $account,
        public AccessRevocationReason $reason,
        public ?AccountActor $actor = null,
    ) {}
}
