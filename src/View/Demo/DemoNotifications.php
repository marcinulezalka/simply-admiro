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

use Simplysmart\Mainboard\Enums\ToastType;
use Simplysmart\Mainboard\View\Notifications\NotificationItem;
use Simplysmart\Mainboard\View\Notifications\NotificationPanel;
use Simplysmart\Mainboard\View\Toast;

/**
 * Przykładowe powiadomienia i toasty widoków design systemu (dziedzina faktur z DemoInvoices).
 */
final class DemoNotifications
{
    public static function panel(?string $allUrl = null): NotificationPanel
    {
        $overdue = DemoInvoices::overdue()[0] ?? DemoInvoices::featured();
        $featured = DemoInvoices::featured();

        return new NotificationPanel([
            new NotificationItem(text('design_system.demo.notifications.overdue', ['number' => $overdue->number]), local_date(DemoInvoices::TODAY . ' 08:41', 'LT'), text('design_system.demo.notifications.overdue_body', ['client' => $overdue->client]), $allUrl, true, 'alert-triangle'),
            new NotificationItem(text('design_system.demo.notifications.accepted', ['client' => $featured->client]), local_date(DemoInvoices::TODAY . ' 07:58', 'LT'), null, $allUrl, true, 'check-circle'),
            new NotificationItem(text('design_system.demo.notifications.export'), local_date('2026-10-22 16:20', 'L LT'), null, $allUrl, false, 'invoice'),
            new NotificationItem(text('design_system.demo.notifications.device'), local_date('2026-10-21 18:03', 'L LT'), text('design_system.demo.notifications.device_body'), $allUrl, false, 'lock'),
        ], $allUrl);
    }

    /**
     * Po jednym toaście każdego typu: powodzenie z akcją, informacja, ostrzeżenie, błąd.
     *
     * @return list<Toast>
     */
    public static function toasts(string $actionUrl): array
    {
        return [
            new Toast(ToastType::Success, text('design_system.demo.toasts.success'), actionLabel: text_action('view'), actionUrl: $actionUrl),
            new Toast(ToastType::Info, text('design_system.demo.toasts.info')),
            new Toast(ToastType::Warning, text('design_system.demo.toasts.warning')),
            new Toast(ToastType::Danger, text('design_system.demo.toasts.danger'), text('design_system.demo.toasts.danger_title')),
        ];
    }
}
