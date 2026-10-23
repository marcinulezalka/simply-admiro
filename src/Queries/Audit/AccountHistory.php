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

namespace Simplysmart\Mainboard\Queries\Audit;

use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Mainboard\Models\AuditEvent;

/**
 * Historia bezpieczeństwa jednego konta strefy dla jego właściciela (strona „Bezpieczeństwo”): ostatnie logowania,
 * błędne PIN-y, blokady, wylogowania i zmiany dostępu z dziennika `audit_events`, od najnowszych. Bez adresów IP
 * i działań administratorów w panelach.
 */
final class AccountHistory
{
    /**
     * Zdarzenia dziennika pokazywane właścicielowi (nazwy z Audit\AuditEntries).
     *
     * @var list<string>
     */
    public const array EVENTS = [
        'sign-in',
        'pin rejected',
        'account locked',
        'sign-out',
        'access revoked',
        'pin changed',
        'trusted device registered',
        'trusted devices revoked',
    ];

    public const int LIMIT = 15;

    /**
     * @return Collection<int, AuditEvent>
     */
    public function recent(string $zone, int|string $accountId, int $limit = self::LIMIT): Collection
    {
        $query = AuditEvent::query();
        $query->where('zone', $zone);
        $query->where('account_id', (string) $accountId);
        $query->whereIn('event', self::EVENTS);
        $query->orderByDesc('id');
        $query->limit($limit);

        return $query->get();
    }

    /**
     * Etykieta zdarzenia dla właściciela konta (`account.history.events.<zdarzenie>`).
     */
    public static function label(AuditEvent $event): string
    {
        return text('account.history.events.' . str_replace([' ', '-'], '_', $event->event));
    }

    /**
     * Szczegół zdarzenia bez danych wrażliwych: nazwa zaufanego urządzenia albo liczba odwołanych urządzeń.
     */
    public static function detail(AuditEvent $event): string
    {
        $context = $event->context ?? [];

        return match ($event->event) {
            'trusted device registered' => is_string($context['device'] ?? null) ? $context['device'] : '',
            'trusted devices revoked' => text('account.history.devices_count', ['count' => is_int($context['count'] ?? null) ? $context['count'] : 0]),
            default => '',
        };
    }
}
