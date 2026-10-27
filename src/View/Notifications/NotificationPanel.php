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

namespace Simplysmart\Admiro\View\Notifications;

/**
 * Panel powiadomień layoutu aplikacji: ostatnie powiadomienia i adres pełnej listy, a w zakładce dziennika — gdy
 * strefa go udostępnia (Contracts\ZoneNotifications) — ostatnie wpisy dziennika zdarzeń i adres pełnego dziennika.
 */
final readonly class NotificationPanel
{
    /**
     * @param list<NotificationItem> $items Ostatnie powiadomienia, od najnowszych.
     * @param list<NotificationItem>|null $auditItems Ostatnie wpisy dziennika zdarzeń, od najnowszych; null = bez zakładki dziennika.
     */
    public function __construct(
        public array $items,
        public ?string $allUrl = null,
        public ?array $auditItems = null,
        public ?string $auditUrl = null,
    ) {}

    public function unreadCount(): int
    {
        return count(array_filter($this->items, static fn(NotificationItem $item): bool => $item->unread));
    }
}
