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

use Simplysmart\Mainboard\Contracts\HasZoneRoles;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Auth\AccessRevocationReason;
use Simplysmart\Mainboard\Auth\AccountActor;
use Simplysmart\Mainboard\Auth\RevokeAccountAccess;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Zones\AccountZone;
use Spatie\Permission\PermissionRegistrar;

/**
 * Członkostwo konta strefy w organizacji: dodanie (bez ról) i usunięcie razem z rolami, uprawnieniami
 * bezpośrednimi i odmowami konta w tej organizacji. Usunięcie unieważnia sesje konta.
 */
final readonly class OrganizationMembership
{
    public function __construct(
        private ConnectionInterface $db,
        private RevokeAccountAccess $revoke,
        private PermissionRegistrar $registrar,
        private AccountZone $zones,
    ) {}

    public function add(Model&CodeAndPinAccount $account, Organization $organization): void
    {
        $this->db->table(PermissionTables::ORGANIZATION_MEMBERS)->insertOrIgnore([
            ...$this->member($account, $organization),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function remove(Model&CodeAndPinAccount $account, Organization $organization, ?AccountActor $actor = null): void
    {
        $member = $this->member($account, $organization);

        $this->db->transaction(function () use ($member): void {
            foreach ([PermissionTables::MODEL_HAS_ROLES, PermissionTables::MODEL_HAS_PERMISSIONS, PermissionTables::PERMISSION_DENIALS, PermissionTables::ORGANIZATION_MEMBERS] as $table) {
                $this->db->table($table)->where($member)->delete();
            }
        });

        if ($account instanceof HasZoneRoles) {
            $account->forgetZonePermissions();
        }
        $this->registrar->forgetCachedPermissions();
        $this->revoke->handle($this->zones->of($account), $account, AccessRevocationReason::PermissionsReduced, $actor);
    }

    /**
     * @return array<string, mixed>
     */
    private function member(Model $account, Organization $organization): array
    {
        return [
            PermissionTables::TEAM_FOREIGN_KEY => $organization->getKey(),
            PermissionTables::MODEL_TYPE => $account->getMorphClass(),
            PermissionTables::MODEL_ID => $account->getKey(),
        ];
    }
}
