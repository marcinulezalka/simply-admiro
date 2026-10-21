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

use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\BlindIndex;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Konta modelu bieżącej strefy, wyszukiwane po `email_hash`.
 */
final readonly class EloquentAccountLocator implements AccountLocator
{
    public function __construct(
        private CurrentZone $zone,
        private BlindIndex $index,
    ) {}

    public function findByEmail(string $email): (Model&CodeAndPinAccount)|null
    {
        $model = $this->zone->get()->accountModel();
        $account = $model::query()->where(AccountColumns::EMAIL_HASH, $this->index->hash(EmailAddress::normalize($email)))->first();

        return $account instanceof CodeAndPinAccount ? $account : null;
    }
}
