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

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Simplysmart\Mainboard\Auth\AccessRevocationReason;
use Simplysmart\Mainboard\Auth\AccountActor;
use Simplysmart\Mainboard\Auth\RevokeAccountAccess;
use Simplysmart\Mainboard\Contracts\CurrentOrganization;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\Permissions\Role;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Permissions\Events\ZoneRoleChanged;
use Simplysmart\Mainboard\Queries\Organizations\ActiveMemberships;
use Simplysmart\Mainboard\Zones\AccountZone;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Nadawanie i odbieranie ról kont strefy w organizacji bez eskalacji uprawnień: nadający działa w tej samej
 * strefie, oba konta są członkami organizacji (w strefie bez `teams`: organizacja operatora, członkostwo
 * dopisywane przy nadaniu), a wszystkie uprawnienia roli (po spłaszczeniu, z odmowami) nadający ma sam
 * w tej organizacji. Np. właściciel nie nada roli z uprawnieniem, którego mu odjęto.
 * Odebranie roli unieważnia sesje konta (uprawnienia maleją).
 */
final readonly class ZoneRoleAssignment
{
    public function __construct(
        private AccountZone $zones,
        private ActiveMemberships $memberships,
        private CurrentOrganization $organization,
        private PermissionCoverage $coverage,
        private RevokeAccountAccess $revoke,
        private Dispatcher $events,
        private OrganizationMembership $membership,
    ) {}

    /**
     * @throws RoleAssignmentDenied
     */
    public function assign(ZoneAccount $assigner, ZoneAccount $account, string $role, Organization $organization): void
    {
        [$zone, $definition] = $this->authorize($assigner, $account, $role, $organization);

        if (! $zone->teams) {
            $this->membership->add($account, $organization);
        }

        $this->organization->within($organization->id, static function () use ($account, $definition): void {
            $account->assignRole($definition);
            $account->unsetRelation('roles');
        });

        $this->events->dispatch(new ZoneRoleChanged($zone->name, $this->id($account), $role, $organization->id, true, $this->actor($assigner)));
    }

    /**
     * @throws RoleAssignmentDenied
     */
    public function revoke(ZoneAccount $assigner, ZoneAccount $account, string $role, Organization $organization): void
    {
        [$zone, $definition] = $this->authorize($assigner, $account, $role, $organization);

        $this->organization->within($organization->id, static function () use ($account, $definition): void {
            $account->removeRole($definition);
            $account->unsetRelation('roles');
        });

        $actor = $this->actor($assigner);
        $this->events->dispatch(new ZoneRoleChanged($zone->name, $this->id($account), $role, $organization->id, false, $actor));
        $this->revoke->handle($zone, $account, AccessRevocationReason::PermissionsReduced, $actor);
    }

    /**
     * @return array{ZoneDefinition, Role}
     */
    private function authorize(ZoneAccount $assigner, ZoneAccount $account, string $role, Organization $organization): array
    {
        $zone = $this->zones->of($account);

        if ($this->zones->of($assigner)->name !== $zone->name) {
            throw RoleAssignmentDenied::because('the assigner belongs to another zone.');
        }

        if (! $zone->teams && $organization->id !== Organization::OPERATOR_ID) {
            throw RoleAssignmentDenied::because("zone [{$zone->name}] has roles only in the operator organization.");
        }

        foreach ($zone->teams ? [$assigner, $account] : [] as $member) {
            if (! $this->memberships->includes($member, $organization->id)) {
                throw RoleAssignmentDenied::because('both accounts must be members of the organization.');
            }
        }

        $definition = Role::query()
            ->where(ColumnNames::NAME, $role)
            ->where(PermissionTables::GUARD_NAME, $zone->guard)
            ->where(static fn($query) => $query->whereNull(PermissionTables::TEAM_FOREIGN_KEY)->orWhere(PermissionTables::TEAM_FOREIGN_KEY, $organization->id))
            ->first() ?? throw RoleAssignmentDenied::because("the role [{$role}] does not exist in zone [{$zone->name}].");

        if (! $this->coverage->covers($zone, $assigner, $definition->permissions()->pluck(ColumnNames::NAME), $organization->id)) {
            throw RoleAssignmentDenied::because("the role [{$role}] grants more than the assigner has.");
        }

        return [$zone, $definition];
    }

    private function actor(ZoneAccount $assigner): AccountActor
    {
        return new AccountActor($this->zones->of($assigner)->name, $this->id($assigner));
    }

    private function id(Model $account): int|string
    {
        $id = $account->getKey();

        return is_int($id) || is_string($id) ? $id : throw new \LogicException('Account key must be an integer or a string.');
    }
}
