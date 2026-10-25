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

use LogicException;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Zones\ZoneDefinition;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

/**
 * Nadanie ról przez system (konsola, zakładanie właściciela strefy): członkostwo w organizacji i role, bez
 * sprawdzania eskalacji. Konto w żądaniu nadaje role wyłącznie przez ZoneRoleAssignment.
 */
final readonly class SystemRoleGrant
{
    public function __construct(
        private OrganizationMembership $membership,
        private CurrentOrganization $organization,
    ) {}

    /**
     * @param list<string> $roles
     *
     * @throws RoleDoesNotExist Gdy roli nie ma w guardzie strefy.
     */
    public function grant(ZoneAccount $account, Organization $organization, array $roles): void
    {
        $this->membership->add($account, $organization);

        if ($roles !== []) {
            $this->organization->within($organization->id, static function () use ($account, $roles): void {
                $account->assignRole($roles);
                $account->forgetZonePermissions();
            });
        }
    }

    /**
     * Organizacja dla ról konta strefy: podana albo, w strefie bez `teams`, organizacja operatora.
     *
     * @throws LogicException Gdy strefa z `teams` nie dostała organizacji.
     */
    public function organizationFor(ZoneDefinition $zone, ?Organization $organization = null): Organization
    {
        if ($organization !== null) {
            return $organization;
        }

        if ($zone->teams) {
            throw new LogicException("Zone [{$zone->name}] works in customer organizations: name the organization.");
        }

        return Organization::query()->findOrFail(Organization::OPERATOR_ID);
    }
}
