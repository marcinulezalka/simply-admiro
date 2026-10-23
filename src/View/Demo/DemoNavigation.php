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

namespace Simplysmart\Mainboard\View\Demo;

use Simplysmart\Mainboard\Enums\DesignSystemView;
use Simplysmart\Mainboard\View\Navigation\NavigationGroup;
use Simplysmart\Mainboard\View\Navigation\NavigationItem;
use Simplysmart\Mainboard\View\Navigation\SearchBox;

/**
 * Menu, wyszukiwarka i konto przykładowego panelu (faktury) w widokach design systemu na x-mainboard::app.layout.
 */
final class DemoNavigation
{
    /**
     * @return list<NavigationGroup>
     */
    public static function groups(DesignSystemView $current): array
    {
        $item = static fn(string $label, DesignSystemView $target, string $icon, ?string $badge = null): NavigationItem => new NavigationItem($label, route('mainboard.design-system.view', $target), $icon, $target === $current, $badge);

        return [
            new NavigationGroup(null, [
                $item(text('design_system.demo.modules.dashboard'), DesignSystemView::Dashboard, 'home'),
                $item(text('design_system.demo.modules.invoices'), DesignSystemView::Sidebar, 'invoice', local_number(count(DemoInvoices::overdue()))),
                $item(text('design_system.demo.modules.clients'), DesignSystemView::Form, 'users'),
                $item(text('design_system.demo.notifications.title'), DesignSystemView::Notifications, 'bell', local_number(DemoNotifications::panel()->unreadCount())),
            ]),
            new NavigationGroup(text('design_system.demo.modules.settings'), [
                $item(text('design_system.views.record'), DesignSystemView::Record, 'standards'),
            ]),
        ];
    }

    /**
     * Przełącznik kontekstu przykładowego panelu: firma, w której pracuje konto (bieżąca oznaczona).
     *
     * @return list<NavigationGroup>
     */
    public static function contexts(DesignSystemView $current): array
    {
        $here = route('mainboard.design-system.view', $current);

        return [
            new NavigationGroup(text('design_system.demo.contexts.company'), [
                new NavigationItem(text('design_system.demo.contexts.office'), $here, 'building', true),
                new NavigationItem(text('design_system.demo.contexts.consulting'), $here, 'building'),
            ]),
        ];
    }

    public static function search(): SearchBox
    {
        return new SearchBox(route('mainboard.design-system.view', DesignSystemView::Sidebar), text('design_system.demo.search'));
    }

    /**
     * @return array{name: string, subtitle: string}
     */
    public static function account(): array
    {
        return ['name' => text('design_system.demo.account'), 'subtitle' => text('design_system.demo.account_subtitle')];
    }
}
