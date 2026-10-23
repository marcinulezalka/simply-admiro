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
 * Panel powiadomień w top barze layoutu aplikacji: ostatnie powiadomienia i adres pełnej listy.
 */
final readonly class NotificationPanel
{
    /**
     * @param list<NotificationItem> $items Ostatnie powiadomienia, od najnowszych.
     */
    public function __construct(
        public array $items,
        public ?string $allUrl = null,
    ) {}

    public function unreadCount(): int
    {
        return count(array_filter($this->items, static fn(NotificationItem $item): bool => $item->unread));
    }
}
