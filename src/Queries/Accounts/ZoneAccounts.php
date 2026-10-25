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

namespace Simplysmart\Mainboard\Queries\Accounts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Auth\AccountColumns;
use Simplysmart\Mainboard\Auth\EmailAddress;
use Simplysmart\Mainboard\Contracts\BlindIndex;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Konta strefy dla paneli administracyjnych: strona listy (od najnowszych) z wyszukiwaniem po dokładnym adresie
 * e-mail (przez indeks `email_hash`, bo adres jest szyfrowany) i konto po tokenie z URL.
 */
final readonly class ZoneAccounts
{
    public function __construct(
        private BlindIndex $index,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Model&CodeAndPinAccount>
     */
    public function page(ZoneDefinition $zone, ?string $email, int $perPage): LengthAwarePaginator
    {
        $model = $zone->accountModel();
        $query = $model::query();

        if ($email !== null && trim($email) !== '') {
            $query->where(AccountColumns::EMAIL_HASH, $this->index->hash(EmailAddress::normalize($email)));
        }

        $query->orderByDesc(ColumnNames::ID);

        return $query->paginate($perPage)->withQueryString();
    }

    public function find(ZoneDefinition $zone, string $token): (Model&CodeAndPinAccount)|null
    {
        $model = $zone->accountModel();
        $account = $model::query()->where(ColumnNames::TOKEN, $token)->first();

        return $account instanceof CodeAndPinAccount ? $account : null;
    }
}
