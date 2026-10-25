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

namespace Simplysmart\Admiro\Permissions;

use Illuminate\Database\ConnectionInterface;
use LogicException;
use Simplysmart\Admiro\Contracts\ZoneOwnerProvisioner;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Organizations\EnrollMember;

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
        private EnrollMember $enroll,
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

        return $this->db->transaction(function () use ($zone, $owner, $organization): string {
            $account = $this->enroll->handle($zone, $organization, $owner->name, $owner->email);
            $this->roles->grant($account, $organization, [RequiredRole::Owner->value]);

            $token = $account->getAttribute(ColumnNames::TOKEN);

            return is_string($token) ? $token : throw new LogicException('Zone accounts must have a route token.');
        });
    }
}
