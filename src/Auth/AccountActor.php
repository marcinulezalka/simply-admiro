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

namespace Simplysmart\Mainboard\Auth;

use Simplysmart\Mainboard\Contracts\SignInAccount;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Kto wykonał akcję na koncie (dziennik): konto ze swoją strefą; brak aktora (null) to system lub konsola.
 */
final readonly class AccountActor
{
    public function __construct(
        public string $zone,
        public int|string $accountId,
    ) {}

    public static function of(ZoneDefinition $zone, SignInAccount $account): self
    {
        $id = $account->getAuthIdentifier();

        return new self($zone->name, is_int($id) || is_string($id) ? $id : throw new \LogicException('Account identifier must be an integer or a string.'));
    }

    /**
     * @return array{zone: string, account_id: int|string}
     */
    public function toLogContext(): array
    {
        return ['zone' => $this->zone, 'account_id' => $this->accountId];
    }
}
