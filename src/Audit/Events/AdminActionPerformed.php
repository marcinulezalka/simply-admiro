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

namespace Simplysmart\Mainboard\Audit\Events;

use Simplysmart\Mainboard\Auth\AccountActor;

/**
 * Akcja w panelu administracyjnym (np. zmiana organizacji, ustawień, założenie właściciela): trafia do
 * dziennika zdarzeń. Akcje, które mają własne zdarzenia (role, unieważnienie dostępu), ich nie powtarzają.
 *
 */
final readonly class AdminActionPerformed
{
    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        public string $action,
        public AccountActor $actor,
        public ?string $zone = null,
        public int|string|null $accountId = null,
        public ?int $organizationId = null,
        public array $context = [],
    ) {}
}
