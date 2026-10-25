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

use Simplysmart\Mainboard\Contracts\SignInAccount;

/**
 * Właściciel konta potwierdził nowy zapasowy adres e-mail (`set`) albo go usunął (dziennik bez adresu).
 */
final readonly class RecoveryEmailChanged
{
    public function __construct(
        public string $zone,
        public SignInAccount $account,
        public bool $set,
        public ?string $ip,
    ) {}
}
