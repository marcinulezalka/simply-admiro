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

namespace Simplysmart\Admiro\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Simplysmart\Admiro\Contracts\AccountLocator;
use Simplysmart\Admiro\Contracts\BlindIndex;
use Simplysmart\Admiro\Contracts\CodeAndPinAccount;
use Simplysmart\Admiro\Contracts\CurrentZone;

/**
 * Konta modelu bieżącej strefy, wyszukiwane po `email_hash`; konta usunięte (soft delete) nie logują się,
 * ale ich adres pozostaje zajęty.
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
        $account = $model::query()->where(AccountColumns::EMAIL_HASH, $this->emailHash($email))->first();

        return $account instanceof CodeAndPinAccount ? $account : null;
    }

    public function isEmailTaken(string $email): bool
    {
        $model = $this->zone->get()->accountModel();

        return $model::query()->withoutGlobalScope(SoftDeletingScope::class)->where(AccountColumns::EMAIL_HASH, $this->emailHash($email))->exists();
    }

    private function emailHash(string $email): string
    {
        return $this->index->hash(EmailAddress::normalize($email));
    }
}
