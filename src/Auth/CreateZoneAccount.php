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
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Zakłada aktywne konto bieżącej strefy bez PIN-u: PIN ustawia właściciel przy pierwszym logowaniu.
 *
 * Dla stref bez rejestracji (np. administratorzy), wywoływane z komendy paczki strefy.
 */
final readonly class CreateZoneAccount
{
    public function __construct(
        private CurrentZone $zone,
        private AccountLocator $accounts,
    ) {}

    /**
     * @throws AccountAlreadyExists Gdy strefa ma już konto z tym adresem.
     */
    public function handle(string $name, string $email): Model&CodeAndPinAccount
    {
        $zone = $this->zone->get();

        if ($this->accounts->findByEmail($email) !== null) {
            throw AccountAlreadyExists::inZone($zone->name);
        }

        $model = $zone->accountModel();
        $account = new $model();
        $account->forceFill([
            AccountColumns::NAME => $name,
            AccountColumns::EMAIL => $email,
            AccountColumns::IS_ACTIVE => true,
        ])->save();

        return $account;
    }
}
