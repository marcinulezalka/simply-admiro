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

namespace Simplysmart\Mainboard\View\Notifications;

/**
 * Powiadomienie w panelu top baru i na liście powiadomień: tytuł, treść, czas (już sformatowany), adres i stan.
 */
final readonly class NotificationItem
{
    /**
     * @param string $time Czas do pokazania, sformatowany przez `local_*` (np. „dziś, 08:41”).
     * @param string $icon Ikona Core (x-mainboard::icon).
     */
    public function __construct(
        public string $title,
        public string $time,
        public ?string $body = null,
        public ?string $url = null,
        public bool $unread = false,
        public string $icon = 'bell',
    ) {}
}
