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

use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Contracts\ZonePermissionChecker;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Czy konto ma w organizacji wszystkie podane uprawnienia (z odmowami). Podstawa ochrony przed eskalacją:
 * nie nada się roli ani nie zarządza kontem, które może więcej niż my.
 */
final readonly class PermissionCoverage
{
    public function __construct(
        private CurrentOrganization $organization,
        private ZonePermissionChecker $permissions,
    ) {}

    /**
     * @param iterable<mixed> $permissions Nazwy uprawnień.
     */
    public function covers(ZoneDefinition $zone, ZoneAccount $account, iterable $permissions, int $organizationId): bool
    {
        return $this->organization->within($organizationId, function () use ($zone, $account, $permissions): bool {
            $account->forgetZonePermissions();

            foreach ($permissions as $permission) {
                if (! is_string($permission) || ! $this->permissions->allows($zone, $account, $permission)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Wszystkie uprawnienia konta w organizacji (z ról i bezpośrednie, przed odmowami).
     *
     * @return list<string>
     */
    public function of(ZoneAccount $account, int $organizationId): array
    {
        return $this->organization->within($organizationId, static function () use ($account): array {
            $account->forgetZonePermissions();
            $names = $account->getAllPermissions()->pluck('name')->all();

            return array_values(array_filter($names, is_string(...)));
        });
    }
}
