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

namespace Simplysmart\Mainboard\Organizations;

use LogicException;
use Simplysmart\Mainboard\Auth\CreateZoneAccount;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Permissions\OrganizationMembership;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

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
