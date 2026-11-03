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

use Illuminate\Contracts\Events\Dispatcher;
use Simplysmart\Admiro\Auth\AccessRevocationReason;
use Simplysmart\Admiro\Auth\AccountKey;
use Simplysmart\Admiro\Auth\AccountActor;
use Simplysmart\Admiro\Auth\RevokeAccountAccess;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\Permissions\Role;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\Events\ZoneRoleChanged;
use Simplysmart\Admiro\Queries\Organizations\ActiveMemberships;
use Simplysmart\Admiro\Zones\AccountZone;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Nadawanie i odbieranie ról kont strefy w organizacji bez eskalacji uprawnień: nadający działa w tej samej
 * strefie i jest członkiem organizacji albo jednej z organizacji nadrzędnych (np. placówka dla swoich departamentów
 * i podorganizacji — uprawnienia spływają w dół drzewa), a wszystkie uprawnienia roli (po spłaszczeniu, z odmowami)
 * ma sam w tej organizacji, w której jest członkiem (najbliższej w górę drzewa). Konto, któremu nadaje się rolę, musi
 * być członkiem tej organizacji albo organizacji nad nią w zasięgu nadającego (np. osoba placówki — rola w jej
 * departamencie) i dostaje przy nadaniu członkostwo w organizacji; odebrać rolę można tylko członkowi organizacji
 * (w strefie bez `teams`: tylko organizacja operatora). Np. właściciel nie nada roli z uprawnieniem, którego mu odjęto, chyba że jego rola tę
 * rolę deleguje (`assigns` w pliku ról, RoleDelegation).
 * Odebranie roli unieważnia sesje konta (uprawnienia maleją).
 */
final readonly class ZoneRoleAssignment
{
    /** Najgłębsze drzewo organizacji przeszukiwane w górę (ochrona przed cyklem w danych). */
    private const int MAX_DEPTH = 16;

    public function __construct(
        private AccountZone $zones,
        private ActiveMemberships $memberships,
        private CurrentOrganization $organization,
        private PermissionCoverage $coverage,
        private RevokeAccountAccess $revoke,
        private Dispatcher $events,
        private OrganizationMembership $membership,
        private RoleDelegation $delegation,
    ) {}

    /**
     * @throws RoleAssignmentDenied
     */
    public function assign(ZoneAccount $assigner, ZoneAccount $account, string $role, Organization $organization): void
    {
        [$zone, $definition] = $this->authorize($assigner, $account, $role, $organization, assigning: true);

        if (! $this->memberships->includes($account, $organization->id)) {
            $this->membership->add($account, $organization);
        }

        $this->organization->within($organization->id, static function () use ($account, $definition): void {
            $account->assignRole($definition);
            $account->forgetZonePermissions();
        });

        $this->events->dispatch(new ZoneRoleChanged($zone->name, AccountKey::of($account), $role, $organization->id, true, $this->actor($assigner)));
    }

    /**
     * @throws RoleAssignmentDenied
     */
    public function revoke(ZoneAccount $assigner, ZoneAccount $account, string $role, Organization $organization): void
    {
        [$zone, $definition] = $this->authorize($assigner, $account, $role, $organization, assigning: false);

        $this->organization->within($organization->id, static function () use ($account, $definition): void {
            $account->removeRole($definition);
            $account->forgetZonePermissions();
        });

        $actor = $this->actor($assigner);
        $this->events->dispatch(new ZoneRoleChanged($zone->name, AccountKey::of($account), $role, $organization->id, false, $actor));
        $this->revoke->handle($zone, $account, AccessRevocationReason::PermissionsReduced, $actor);
    }

    /**
     * @return array{ZoneDefinition, Role}
     */
    private function authorize(ZoneAccount $assigner, ZoneAccount $account, string $role, Organization $organization, bool $assigning): array
    {
        $zone = $this->zones->of($account);

        if ($this->zones->of($assigner)->name !== $zone->name) {
            throw RoleAssignmentDenied::because('the assigner belongs to another zone.');
        }

        if (! $zone->teams && $organization->id !== Organization::OPERATOR_ID) {
            throw RoleAssignmentDenied::because("zone [{$zone->name}] has roles only in the operator organization.");
        }

        $authority = $zone->teams ? $this->authorityOf($assigner, $organization) : $organization;

        if ($authority === null) {
            throw RoleAssignmentDenied::because('the assigner is not a member of the organization or of any organization above it.');
        }

        $accountReach = $zone->teams ? $this->authorityOf($account, $organization) : $organization;
        $accountInTree = $accountReach !== null && ($assigning ? $this->within($accountReach, $authority) : $accountReach->id === $organization->id);

        if ($zone->teams && ! $accountInTree) {
            throw RoleAssignmentDenied::because('both accounts must be members of the organization (or of an organization above it within the assigner\'s reach).');
        }

        $definition = Role::query()
            ->where(ColumnNames::NAME, $role)
            ->where(PermissionTables::GUARD_NAME, $zone->guard)
            ->where(static fn($query) => $query->whereNull(PermissionTables::TEAM_FOREIGN_KEY)->orWhere(PermissionTables::TEAM_FOREIGN_KEY, $organization->id))
            ->first() ?? throw RoleAssignmentDenied::because("the role [{$role}] does not exist in zone [{$zone->name}].");

        if (
            ! $this->coverage->covers($zone, $assigner, $definition->permissions()->pluck(ColumnNames::NAME), $authority->id)
            && ! $this->delegation->allows($assigner, $role, $authority)
        ) {
            throw RoleAssignmentDenied::because("the role [{$role}] grants more than the assigner has.");
        }

        return [$zone, $definition];
    }

    /**
     * Organizacja, z której nadający czerpie uprawnienia: ta sama, gdy jest jej członkiem, a inaczej najbliższa
     * organizacja nadrzędna z jego członkostwem; null — poza drzewem nadającego.
     */
    private function authorityOf(ZoneAccount $assigner, Organization $organization): ?Organization
    {
        $memberships = $this->memberships->ids($assigner);

        for ($current = $organization, $depth = 0; $current !== null && $depth < self::MAX_DEPTH; $current = $current->parent, $depth++) {
            if (in_array($current->id, $memberships, true)) {
                return $current;
            }
        }

        return null;
    }

    /**
     * Czy organizacja leży w poddrzewie organizacji uprawnień nadającego (jest nią albo pod nią).
     */
    private function within(Organization $organization, Organization $authority): bool
    {
        for ($current = $organization, $depth = 0; $current !== null && $depth < self::MAX_DEPTH; $current = $current->parent, $depth++) {
            if ($current->id === $authority->id) {
                return true;
            }
        }

        return false;
    }

    private function actor(ZoneAccount $assigner): AccountActor
    {
        return new AccountActor($this->zones->of($assigner)->name, AccountKey::of($assigner));
    }
}
