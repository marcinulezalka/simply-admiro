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

use Closure;
use Simplysmart\Admiro\Contracts\CurrentOrganization;
use Simplysmart\Admiro\Contracts\CurrentRole;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Http\Controllers\Account\RoleSwitchController;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\RoleSelector;
use Simplysmart\Admiro\Queries\Organizations\ActiveMemberships;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Admiro\View\Navigation\NavigationForm;
use Simplysmart\Admiro\View\Navigation\NavigationGroup;
use Simplysmart\Admiro\View\Navigation\NavigationItem;

/**
 * Konteksty konta w przełączniku layoutu strefy: organizacje, w których konto jest członkiem (strefa z `teams`;
 * organizacja, jednostka podrzędna — np. placówka — albo departament — np. poradnia). W strefie z `role_contexts`
 * kontekstem jest tylko organizacja, w której konto ma rolę: grupa z nazwą i ikoną rodzaju (KIND_ICONS — budynek przy
 * organizacji i jednostce; nazwa rodzaju w podpowiedzi) z rolami konta w niej (rola administracyjna — delegująca inne
 * role, ZoneRoleNames::administrative() — z wypełnionym ludzikiem ADMIN_ROLE_ICON, pozostałe z konturem ROLE_ICON);
 * członkostwo bez ról (np. poradnia grupująca gabinety) nie jest kontekstem. W strefie bez ról kontekstowych
 * organizacje są pozycjami listy organizacji. Pozycja to formularz POST na `account.context.switch` z polem FIELD = `<token organizacji>:<rola>`,
 * więc jedno kliknięcie ustawia organizację i rolę z jej uprawnieniami. Nazwy rodzajów: `account.context_kinds.*`
 * (strefa nadpisuje je w swoich tekstach, ZoneDefinition::textKey()). Przełącznik jest widoczny już przy jednym kontekście
 * (pokazuje bieżący); bez kontekstów — brak przełącznika. W strefie z `context_in_url` pozycja to odnośnik na pulpit
 * z kluczem kontekstu (Zones\ContextKeys) zamiast formularza, więc każda karta przeglądarki może pracować w innym
 * kontekście.
 */
final readonly class ZoneContexts
{
    public const string FIELD = 'context';

    public const string SEPARATOR = ':';

    /**
     * Ikona rodzaju organizacji przed jej nazwą w przełączniku (nazwa rodzaju tylko w podpowiedzi i dla czytników).
     *
     * @var array<string, string>
     */
    private const array KIND_ICONS = ['organization' => 'building', 'unit' => 'building', 'department' => 'grid'];

    /** Ikona roli w przełączniku: zwykła rola (kontur ludzika) i rola administracyjna (wypełniony ludzik). */
    private const string ROLE_ICON = 'user';

    private const string ADMIN_ROLE_ICON = 'user-filled';

    public function __construct(
        private ActiveMemberships $memberships,
        private ZoneRoleNames $roles,
        private CurrentOrganization $organization,
        private CurrentRole $role,
        private ContextKeys $keys,
        private RoleSelector $roleSelector,
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
        $roleIcon = $this->roleIcon($zone);
        $groups = [];
        $plain = [];
        $count = 0;

        foreach ($this->memberships->organizations($account)->sortBy(static fn(Organization $organization): string => $organization->contextName()) as $organization) {
            $isCurrent = $organization->id === $currentId;
            $roles = $zone->roleContexts ? $this->roles->of($account, $organization->id) : [];

            if ($roles === [] && $zone->roleContexts) {
                continue;
            }

            if ($roles === []) {
                $plain[] = $this->item($zone, $account, $organization->contextName(), $action, self::FIELD, self::value($organization, null), self::KIND_ICONS['organization'], $isCurrent, $organization->id, null);
                $count++;

                continue;
            }

            $active = $isCurrent ? ($this->role->of($account, $organization->id) ?? (count($roles) === 1 ? $roles[0] : null)) : null;
            $kind = self::kind($organization);
            $groups[] = new NavigationGroup(
                $organization->contextName(),
                array_map(fn(string $role): NavigationForm|NavigationItem => $this->item($zone, $account, $zone->roleLabel($role), $action, self::FIELD, self::value($organization, $role), $roleIcon($role), $role === $active, $organization->id, $role), $roles),
                self::KIND_ICONS[$kind],
                text($zone->textKey('account.context_kinds.' . $kind)),
            );
            $count += count($roles);
        }

        if ($plain !== []) {
            $groups[] = new NavigationGroup(text($zone->textKey('account.organization')), $plain);
        }

        return $count > 0 ? $groups : [];
    }

    /**
     * Jednostki konta, które są jego kontekstami (organizacje, jednostki i departamenty z rolą konta — w strefie
     * z `role_contexts`; inaczej każde członkostwo), z nazwą i ikoną rodzaju i znacznikiem bieżącej — np. lista
     * jednostek w menu konta layoutu stref; strefa bez `teams` — lista pusta.
     *
     * Każda jednostka ma też rolę konta w niej (najszerszą — RoleSelector::broadest(); bieżąca jednostka: aktywną)
     * i adres jej pulpitu w tym kontekście (`url`; w strefie z `context_in_url` z kluczem kontekstu).
     *
     * @return list<array{organization: Organization, kind: string, icon: string, current: bool, role: ?string, url: string}>
     */
    public function units(ZoneDefinition $zone, ZoneAccount $account): array
    {
        if (! $zone->teams) {
            return [];
        }

        $units = [];

        foreach ($this->memberships->organizations($account)->sortBy(static fn(Organization $organization): string => $organization->contextName()) as $organization) {
            if ($zone->roleContexts && $this->roles->of($account, $organization->id) === []) {
                continue;
            }

            $kind = self::kind($organization);
            $current = $organization->id === $this->organization->id();
            $role = $current ? $this->role->of($account, $organization->id) : null;
            $role ??= $zone->roleContexts ? $this->roleSelector->broadest($zone, $this->roles->of($account, $organization->id)) : null;
            $units[] = [
                'organization' => $organization,
                'kind' => text($zone->textKey('account.context_kinds.' . $kind)),
                'icon' => self::KIND_ICONS[$kind],
                'current' => $current,
                'role' => $role,
                'url' => route($zone->routeName(ZoneRedirects::HOME_ROUTE), $zone->contextInUrl ? [ContextKeys::PARAMETER => $this->keys->key($zone, $account, $organization->id, $role)] : []),
            ];
        }

        return $units;
    }

    /**
     * Strefa bez `teams`, ale z `role_contexts`: same role konta w bieżącej organizacji (account.role.switch).
     *
     * @return list<NavigationGroup>
     */
    private function rolesOnly(ZoneDefinition $zone, ZoneAccount $account, ?int $organizationId): array
    {
        $roles = $zone->roleContexts && $organizationId !== null ? $this->roles->of($account, $organizationId) : [];

        if ($roles === []) {
            return [];
        }

        $active = $this->role->of($account, $organizationId);
        $action = route($zone->routeName(AccountRoutes::ROLE_SWITCH));
        $roleIcon = $this->roleIcon($zone);

        return [new NavigationGroup(text($zone->textKey('account.role')), array_map(
            fn(string $role): NavigationForm|NavigationItem => $this->item($zone, $account, $zone->roleLabel($role), $action, RoleSwitchController::FIELD, $role, $roleIcon($role), $role === $active, $organizationId, $role),
            $roles,
        ))];
    }

    /**
     * Pozycja kontekstu: formularz POST przełączenia albo — w strefie z `context_in_url` — odnośnik na pulpit z kluczem.
     */
    private function item(ZoneDefinition $zone, ZoneAccount $account, string $label, string $action, string $field, string $value, string $icon, bool $current, int $organizationId, ?string $role): NavigationForm|NavigationItem
    {
        if (! $zone->contextInUrl) {
            return new NavigationForm($label, $action, $field, $value, $icon, $current);
        }

        return new NavigationItem($label, route($zone->routeName(ZoneRedirects::HOME_ROUTE), [ContextKeys::PARAMETER => $this->keys->key($zone, $account, $organizationId, $role)]), $icon, $current);
    }

    /**
     * Ikona roli: wypełniony ludzik dla ról administracyjnych strefy, kontur dla pozostałych.
     *
     * @return Closure(string): string
     */
    private function roleIcon(ZoneDefinition $zone): Closure
    {
        $administrative = $this->roles->administrative($zone);

        return static fn(string $role): string => in_array($role, $administrative, true) ? self::ADMIN_ROLE_ICON : self::ROLE_ICON;
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
