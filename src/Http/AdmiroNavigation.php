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

namespace Simplysmart\Admiro\Http;

use Illuminate\Http\Request;
use Simplysmart\Admiro\Enums\Permission;
use Simplysmart\Mainboard\Contracts\ZoneNavigation;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Queries\Permissions\ZoneRoleNames;
use Simplysmart\Mainboard\View\Navigation\NavigationGroup;
use Simplysmart\Mainboard\View\Navigation\NavigationItem;
use Simplysmart\Mainboard\View\Navigation\SearchBox;

/**
 * Menu strefy administratorów w layoucie aplikacji: pulpit oraz grupy stref i kont, organizacji i systemu — każda
 * pozycja tylko z uprawnieniem administratora (puste grupy znikają); druga linia pod nazwą konta: role
 * administratora w organizacji operatora. Bez wyszukiwarki (konta szuka się w strefie).
 */
final readonly class AdmiroNavigation implements ZoneNavigation
{
    public function __construct(
        private AdminContext $context,
        private Request $request,
        private ZoneRoleNames $roles,
    ) {}

    public function groups(): array
    {
        $groups = [
            new NavigationGroup(null, [$this->item('dashboard', 'admiro::dashboard.title', 'home')]),
            new NavigationGroup(text('admiro::navigation.groups.zones'), $this->items(
                $this->itemIf(Permission::ZonesView, 'zones.index', 'admiro::admin.zones_title', 'zones', 'zones.*'),
                $this->itemIf(Permission::OwnersAssign, 'owners.create', 'admiro::admin.owner_title', 'user-plus', 'owners.*'),
                $this->itemIf(Permission::UsersManage, 'administrators.index', 'admiro::admin.administrators_title', 'shield-check', 'administrators.*'),
            )),
            new NavigationGroup(text('admiro::navigation.groups.organizations'), $this->items(
                $this->itemIf(Permission::OrganizationsView, 'organizations.index', 'admiro::admin.organizations_title', 'building', 'organizations.*'),
            )),
            new NavigationGroup(text('admiro::navigation.groups.system'), $this->items(
                $this->itemIf(Permission::AuditView, 'audit.index', 'admiro::admin.audit_title', 'history', 'audit.*'),
                $this->itemIf(Permission::SettingsView, 'settings.show', 'admiro::admin.settings_title', 'settings', 'settings.*'),
                $this->itemIf(Permission::SettingsView, 'dictionaries.index', 'admiro::admin.dictionaries_title', 'dictionaries', 'dictionaries.*', ['dictionary' => 'countries']),
                $this->itemIf(Permission::SettingsView, 'exchange-rates.index', 'admiro::admin.exchange_rates_title', 'credit-card', 'exchange-rates.*'),
                $this->itemIf(Permission::SettingsView, 'cookie-policy.index', 'admiro::admin.cookie_policy_title', 'shield-check', 'cookie-policy.*'),
            )),
        ];

        return array_values(array_filter($groups, static fn(NavigationGroup $group): bool => $group->items !== []));
    }

    public function search(): ?SearchBox
    {
        return null;
    }

    public function subtitle(): ?string
    {
        $roles = array_map($this->context->zone()->roleLabel(...), $this->roles->of($this->context->admin(), Organization::OPERATOR_ID));

        return $roles === [] ? null : implode(' · ', $roles);
    }

    /**
     * @return list<NavigationItem>
     */
    private function items(?NavigationItem ...$items): array
    {
        return array_values(array_filter($items));
    }

    /**
     * @param array<string, string> $parameters
     */
    private function itemIf(Permission $permission, string $route, string $label, string $icon, string $active, array $parameters = []): ?NavigationItem
    {
        return $this->context->can($permission) ? $this->item($route, $label, $icon, $active, $parameters) : null;
    }

    /**
     * @param array<string, string> $parameters
     */
    private function item(string $route, string $label, string $icon, ?string $active = null, array $parameters = []): NavigationItem
    {
        $zone = $this->context->zone();

        return new NavigationItem(text($label), route($zone->routeName($route), $parameters), $icon, $this->request->routeIs($zone->routeName($active ?? $route)));
    }
}
