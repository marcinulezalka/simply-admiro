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

namespace Simplysmart\Admiro\Organizations;

use LogicException;
use Simplysmart\Admiro\Auth\CreateZoneAccount;
use Simplysmart\Admiro\Contracts\AccountLocator;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\OrganizationMembership;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Dopisuje osobę do organizacji w strefie: istniejące konto o tym adresie albo nowe (aktywne, bez PIN-u),
 * członkostwo bez ról. Konto powstaje w tabeli strefy, także gdy żądanie należy do innej strefy.
 */
final readonly class EnrollMember
{
    public function __construct(
        private CurrentZone $current,
        private AccountLocator $accounts,
        private CreateZoneAccount $create,
        private OrganizationMembership $membership,
    ) {}

    /**
     * @throws LogicException Gdy model kont strefy nie dziedziczy po ZoneAccount.
     */
    public function handle(ZoneDefinition $zone, Organization $organization, string $name, string $email): ZoneAccount
    {
        return $this->current->within($zone, function (ZoneDefinition $zone) use ($organization, $name, $email): ZoneAccount {
            $account = $this->accounts->findByEmail($email) ?? $this->create->handle($name, $email);

            if (! $account instanceof ZoneAccount) {
                throw new LogicException("Zone [{$zone->name}] accounts must extend " . ZoneAccount::class . '.');
            }

            $this->membership->add($account, $organization);

            return $account;
        });
    }
}
