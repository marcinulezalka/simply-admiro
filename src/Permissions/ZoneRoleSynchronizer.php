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
use Illuminate\Database\Eloquent\Builder;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Database\IdSlots;
use Simplysmart\Mainboard\Models\Permissions\Permission;
use Simplysmart\Mainboard\Models\Permissions\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Zapisuje role i uprawnienia strefy z ZoneRoleSet: w jednej transakcji, idempotentnie, z id z puli seederów.
 * Rekord usunięty (soft delete) dalej zajmuje swoją nazwę, więc jest przywracany zamiast tworzony. Role są
 * globalne dla guardu strefy (`organization_id` = null). Uprawnień i ról spoza pliku nie usuwa, tylko je
 * raportuje; usuwa (soft delete) dopiero `prune`.
 */
final readonly class ZoneRoleSynchronizer
{
    private const string STALE_PERMISSION = 'permission:';

    private const string STALE_ROLE = 'role:';

    public function __construct(
        private ConnectionInterface $db,
        private ZoneRegistry $zones,
        private IdSlots $idSlots,
        private PermissionRegistrar $registrar,
    ) {}

    public function sync(ZoneRoleSet $set, bool $prune = false): ZoneRoleSyncReport
    {
        $guard = $this->zones->get($set->zone)->guard;
        $created = [];
        $restored = [];

        $stale = $this->db->transaction(function () use ($set, $guard, $prune, &$created, &$restored): array {
            foreach ($set->permissions as $name) {
                $permission = Permission::withTrashed()->where(PermissionTables::GUARD_NAME, $guard)->where(ColumnNames::NAME, $name)->first();
                $this->ensure($permission ?? new Permission(), PermissionTables::PERMISSIONS, self::STALE_PERMISSION . $name, $created, $restored, [
                    ColumnNames::NAME => $name,
                    PermissionTables::GUARD_NAME => $guard,
                ]);
            }

            $this->registrar->forgetCachedPermissions();

            foreach (array_keys($set->roles) as $name) {
                $role = $this->globalRoles($guard, withTrashed: true)->where(ColumnNames::NAME, $name)->first();
                $role = $this->ensure($role ?? new Role(), PermissionTables::ROLES, self::STALE_ROLE . $name, $created, $restored, [
                    ColumnNames::NAME => $name,
                    PermissionTables::GUARD_NAME => $guard,
                    PermissionTables::TEAM_FOREIGN_KEY => null,
                ]);
                $role->syncPermissions($set->permissionsOf($name));
            }

            return $this->stale($set, $guard, $prune);
        });

        $this->registrar->forgetCachedPermissions();

        return new ZoneRoleSyncReport($set->zone, $created, $restored, $stale, $prune);
    }

    /**
     * Nowy rekord dostaje id z puli seederów; usunięty wraca z kosza.
     *
     * @template TModel of Permission|Role
     *
     * @param TModel $record
     * @param list<string> $created
     * @param list<string> $restored
     * @param array<string, mixed> $attributes
     *
     * @return TModel
     */
    private function ensure(Permission|Role $record, string $table, string $label, array &$created, array &$restored, array $attributes): Permission|Role
    {
        if (! $record->exists) {
            $record->forceFill([ColumnNames::ID => $this->idSlots->next($table), ColumnNames::IS_ACTIVE => true, ...$attributes])->save();
            $created[] = $label;
        } elseif ($record->trashed()) {
            $record->restore();
            $restored[] = $label;
        }

        return $record;
    }

    /**
     * @return list<string>
     */
    private function stale(ZoneRoleSet $set, string $guard, bool $prune): array
    {
        $permissions = Permission::query();
        $permissions->where(PermissionTables::GUARD_NAME, $guard);
        $permissions->whereNotIn(ColumnNames::NAME, $set->permissions);

        $roles = $this->globalRoles($guard, withTrashed: false);
        $roles->whereNotIn(ColumnNames::NAME, array_keys($set->roles));

        $stale = [];

        foreach ($permissions->get() as $permission) {
            $stale[] = self::STALE_PERMISSION . $permission->name;

            if ($prune) {
                $permission->delete();
            }
        }

        foreach ($roles->get() as $role) {
            $stale[] = self::STALE_ROLE . $role->name;

            if ($prune) {
                $role->delete();
            }
        }

        return $stale;
    }

    /**
     * Role globalne guardu strefy (własne role organizacji nie pochodzą z pliku).
     *
     * @return Builder<Role>
     */
    private function globalRoles(string $guard, bool $withTrashed): Builder
    {
        $query = $withTrashed ? Role::withTrashed() : Role::query();
        $query->where(PermissionTables::GUARD_NAME, $guard);
        $query->whereNull(PermissionTables::TEAM_FOREIGN_KEY);

        return $query;
    }
}
