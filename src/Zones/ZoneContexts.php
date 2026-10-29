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

namespace Simplysmart\Admiro\Zones;

use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Contracts\CurrentRole;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Http\Controllers\Account\RoleSwitchController;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Queries\Organizations\ActiveMemberships;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Admiro\View\Navigation\NavigationForm;
use Simplysmart\Admiro\View\Navigation\NavigationGroup;

/**
 * Konteksty konta w przełączniku layoutu strefy: wszystkie organizacje, w których konto jest członkiem (strefa
 * z `teams`; organizacja, jednostka podrzędna — np. placówka — albo departament — np. poradnia), każda jako grupa
 * „Rodzaj · nazwa” z rolami konta w niej (strefa z `role_contexts`), a bez ról kontekstowych — jako pozycja listy
 * organizacji. Pozycja to formularz POST na `account.context.switch` z polem FIELD = `<token organizacji>:<rola>`,
 * więc jedno kliknięcie ustawia organizację i rolę z jej uprawnieniami. Nazwy rodzajów: `account.context_kinds.*`
 * (strefa nadpisuje je w swoich tekstach, ZoneDefinition::textKey()). Mniej niż dwa konteksty — brak przełącznika.
 */
final readonly class ZoneContexts
{
    public const string FIELD = 'context';

    public const string SEPARATOR = ':';

    public function __construct(
        private ActiveMemberships $memberships,
        private ZoneRoleNames $roles,
        private CurrentOrganization $organization,
        private CurrentRole $role,
    ) {}

    /**
     * Wartość pola formularza przełączenia: token organizacji i rola (pusta — bez zmiany roli).
     */
    public static function value(Organization $organization, ?string $role): string
    {
        return $organization->token . self::SEPARATOR . ($role ?? '');
    }

    /**
     * @return list<NavigationGroup>
     */
    public function for(ZoneDefinition $zone, ZoneAccount $account): array
    {
        $currentId = $this->organization->id();

        if (! $zone->teams) {
            return $this->rolesOnly($zone, $account, $currentId);
        }

        $action = route($zone->routeName(AccountRoutes::CONTEXT_SWITCH));
        $groups = [];
        $plain = [];
        $count = 0;

        foreach ($this->memberships->organizations($account)->sortBy(static fn(Organization $organization): string => $organization->contextName()) as $organization) {
            $isCurrent = $organization->id === $currentId;
            $roles = $zone->roleContexts ? $this->roles->of($account, $organization->id) : [];

            if ($roles === []) {
                $plain[] = new NavigationForm($organization->contextName(), $action, self::FIELD, self::value($organization, null), 'building', $isCurrent);
                $count++;

                continue;
            }

            $active = $isCurrent ? ($this->role->of($account, $organization->id) ?? (count($roles) === 1 ? $roles[0] : null)) : null;
            $groups[] = new NavigationGroup(
                text($zone->textKey('account.context_kinds.' . self::kind($organization))) . ' · ' . $organization->contextName(),
                array_map(static fn(string $role): NavigationForm => new NavigationForm($zone->roleLabel($role), $action, self::FIELD, self::value($organization, $role), 'user', $role === $active), $roles),
            );
            $count += count($roles);
        }

        if ($plain !== []) {
            $groups[] = new NavigationGroup(text($zone->textKey('account.organization')), $plain);
        }

        return $count > 1 ? $groups : [];
    }

    /**
     * Strefa bez `teams`, ale z `role_contexts`: same role konta w bieżącej organizacji (account.role.switch).
     *
     * @return list<NavigationGroup>
     */
    private function rolesOnly(ZoneDefinition $zone, ZoneAccount $account, ?int $organizationId): array
    {
        $roles = $zone->roleContexts && $organizationId !== null ? $this->roles->of($account, $organizationId) : [];

        if (count($roles) < 2) {
            return [];
        }

        $active = $this->role->of($account, $organizationId);
        $action = route($zone->routeName(AccountRoutes::ROLE_SWITCH));

        return [new NavigationGroup(text($zone->textKey('account.role')), array_map(
            static fn(string $role): NavigationForm => new NavigationForm($zone->roleLabel($role), $action, RoleSwitchController::FIELD, $role, 'user', $role === $active),
            $roles,
        ))];
    }

    /**
     * Rodzaj organizacji w przełączniku: departament, jednostka podrzędna albo organizacja główna.
     */
    private static function kind(Organization $organization): string
    {
        return match (true) {
            $organization->isDepartment() => 'department',
            $organization->parent_id !== null => 'unit',
            default => 'organization',
        };
    }
}
