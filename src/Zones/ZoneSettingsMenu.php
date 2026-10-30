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

use Illuminate\Http\Request;
use Simplysmart\Admiro\Contracts\ZoneNavigation;
use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\View\Navigation\NavigationGroup;
use Simplysmart\Admiro\View\Navigation\NavigationItem;
use Simplysmart\Admiro\View\Navigation\SettingsMenu;

/**
 * Buduje menu ustawień zalogowanego konta: najpierw grupy ustawień strefy (ZoneNavigation::settings()), na końcu
 * grupa konta Core (profil, bezpieczeństwo, wygląd). Strona jest ustawieniem, gdy któraś pozycja jest bieżąca;
 * powrót prowadzi na pulpit strefy.
 */
final readonly class ZoneSettingsMenu
{
    public function __construct(
        private Request $request,
        private ZoneRedirects $redirects,
    ) {}

    public function for(ZoneDefinition $zone, ?ZoneNavigation $navigation): SettingsMenu
    {
        $groups = [...($navigation?->settings() ?? []), new NavigationGroup(text('account.settings_group'), $this->accountItems($zone))];
        $items = array_values(array_filter(
            array_merge(...array_map(static fn(NavigationGroup $group): array => $group->items, $groups)),
            static fn(mixed $item): bool => $item instanceof NavigationItem,
        ));

        return new SettingsMenu(
            $groups,
            $items[0]->url ?? route($zone->routeName(AccountRoutes::PROFILE)),
            array_any($items, static fn(NavigationItem $item): bool => $item->current),
            $this->redirects->userRedirect(),
        );
    }

    /**
     * Pozycje konta Core (także menu konta pod awatarem), bieżąca oznaczona.
     *
     * @return list<NavigationItem>
     */
    public function accountItems(ZoneDefinition $zone): array
    {
        return [
            $this->profileItem($zone),
            $this->item($zone, AccountRoutes::SECURITY, text('account.security_title'), 'shield-check'),
            $this->item($zone, AccountRoutes::APPEARANCE, text('account.appearance_title'), 'contrast'),
        ];
    }

    /**
     * „Mój profil” — jedyna pozycja konta w menu pod awatarem layoutu stref (Enums\AppLayout::Zone), bieżąca oznaczona.
     */
    public function profileItem(ZoneDefinition $zone): NavigationItem
    {
        return $this->item($zone, AccountRoutes::PROFILE, text('account.profile_title'), 'user');
    }

    private function item(ZoneDefinition $zone, string $route, string $label, string $icon): NavigationItem
    {
        return new NavigationItem($label, route($zone->routeName($route)), $icon, $this->request->routeIs($zone->routeName($route)));
    }
}
