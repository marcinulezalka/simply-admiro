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
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\Permissions\Role;
use Simplysmart\Mainboard\Models\ZoneAccount;

/**
 * Role delegowane w pliku ról (`assigns`): posiadacz roli nadaje i odbiera wymienione role mimo braku ich
 * uprawnień, np. właściciel bez uprawnień do danych wrażliwych nadaje rolę specjalisty, także sobie. Liczą się
 * wyłącznie role konta w podanej organizacji, nigdy z innych organizacji. Delegowanie zapisuje
 * ZoneRoleSynchronizer w `json_default_settings` roli, a sprawdza ZoneRoleAssignment.
 */
final readonly class RoleDelegation
{
    /**
     * Klucz w `json_default_settings` roli z listą ról delegowanych.
     */
    public const string SETTING = 'assigns';

    public function __construct(
        private CurrentOrganization $organization,
    ) {}

    /**
     * Czy role konta w organizacji delegują mu rolę `$role`.
     */
    public function allows(ZoneAccount $assigner, string $role, Organization $organization): bool
    {
        return in_array($role, $this->assignableBy($assigner, $organization), true);
    }

    /**
     * Role delegowane kontu przez jego role w organizacji, bez powtórzeń.
     *
     * @return list<string>
     */
    public function assignableBy(ZoneAccount $assigner, Organization $organization): array
    {
        return $this->organization->within($organization->id, static function () use ($assigner): array {
            $assignable = [];

            $held = Role::query()->whereKey($assigner->roles()->pluck(PermissionTables::ROLES . '.' . ColumnNames::ID))->get();

            foreach ($held as $role) {
                $delegated = $role->defaultSetting(self::SETTING, []);

                foreach (is_array($delegated) ? $delegated : [] as $name) {
                    if (is_string($name)) {
                        $assignable[] = $name;
                    }
                }
            }

            return array_values(array_unique($assignable));
        });
    }
}
