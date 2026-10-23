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

namespace Simplysmart\Mainboard\Permissions;

use Illuminate\Database\ConnectionInterface;
use LogicException;
use Simplysmart\Mainboard\Auth\CreateZoneAccount;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\ZoneOwnerProvisioner;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Właściciel strefy z kontem Core (ZoneAccount): konto o tym adresie albo nowe (aktywne, bez PIN-u),
 * członkostwo w organizacji i rola `owner`. Paczka strefy rejestruje go w providerze:
 * `$provisioners->addDefault('shop')`.
 */
final readonly class DefaultOwnerProvisioner implements ZoneOwnerProvisioner
{
    public function __construct(
        private string $zone,
        private ZoneRegistry $zones,
        private CurrentZone $current,
        private AccountLocator $accounts,
        private CreateZoneAccount $create,
        private SystemRoleGrant $roles,
        private ConnectionInterface $db,
    ) {}

    public function zone(): string
    {
        return $this->zone;
    }

    public function provisionOwner(OwnerData $owner, ?Organization $organization = null): string
    {
        $zone = $this->zones->get($this->zone);
        $organization = $this->roles->organizationFor($zone, $organization);

        return $this->db->transaction(fn(): string => $this->current->within($zone, function (ZoneDefinition $zone) use ($owner, $organization): string {
            $account = $this->accounts->findByEmail($owner->email) ?? $this->create->handle($owner->name, $owner->email);

            if (! $account instanceof ZoneAccount) {
                throw new LogicException("Zone [{$zone->name}] accounts must extend " . ZoneAccount::class . '.');
            }

            $this->roles->grant($account, $organization, [RequiredRole::Owner->value]);

            $token = $account->getAttribute(ColumnNames::TOKEN);

            return is_string($token) ? $token : throw new LogicException('Zone accounts must have a route token.');
        }));
    }
}
