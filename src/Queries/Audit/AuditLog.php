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

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Models\AuditEvent;

/**
 * Dziennik zdarzeń dla paneli: strona wpisów od najnowszych z filtrami (strefa, zdarzenie, zakres dat),
 * dziennik jednej organizacji i lista nazw zdarzeń do filtra.
 */
final class AuditLog
{
    /**
     * @return LengthAwarePaginator<int, AuditEvent>
     */
    public function page(?string $zone, ?string $event, ?Carbon $from, ?Carbon $to, int $perPage): LengthAwarePaginator
    {
        $query = AuditEvent::query();

        if ($zone !== null && $zone !== '') {
            $query->where('zone', $zone);
        }

        if ($event !== null && $event !== '') {
            $query->where('event', $event);
        }

        if ($from !== null) {
            $query->where('occurred_at', '>=', $from->copy()->startOfDay());
        }

        if ($to !== null) {
            $query->where('occurred_at', '<=', $to->copy()->endOfDay());
        }

        $query->orderByDesc('id');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Dziennik jednej organizacji: zdarzenia z jej organizacją (role, członkostwa, dostęp do danych) i zdarzenia
     * kont jej członków w strefie (logowania, PIN, urządzenia), od najnowszych; opcjonalnie tylko jedno zdarzenie.
     *
     * @param list<int|string> $accountIds Konta członków organizacji w strefie.
     *
     * @return LengthAwarePaginator<int, AuditEvent>
     */
    public function forOrganization(int $organizationId, string $zone, array $accountIds, int $perPage, ?string $event = null): LengthAwarePaginator
    {
        $query = $this->organizationQuery($organizationId, $zone, $accountIds);

        if ($event !== null && $event !== '') {
            $query->where('event', $event);
        }

        $query->orderByDesc('id');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Nazwy zdarzeń w dzienniku organizacji (filtr dziennika organizacji).
     *
     * @param list<int|string> $accountIds Konta członków organizacji w strefie.
     *
     * @return list<string>
     */
    public function eventsForOrganization(int $organizationId, string $zone, array $accountIds): array
    {
        $query = $this->organizationQuery($organizationId, $zone, $accountIds);
        $query->distinct();
        $query->orderBy('event');

        return array_values(array_filter($query->pluck('event')->all(), is_string(...)));
    }

    /**
     * @param list<int|string> $accountIds
     *
     * @return Builder<AuditEvent>
     */
    private function organizationQuery(int $organizationId, string $zone, array $accountIds): Builder
    {
        $ids = array_map(strval(...), $accountIds);
        $query = AuditEvent::query();
        $query->where(static function ($scope) use ($organizationId, $zone, $ids): void {
            $scope->where('organization_id', $organizationId)
                ->orWhere(static fn($members) => $members->where('zone', $zone)->whereIn('account_id', $ids));
        });

        return $query;
    }

    /**
     * @return list<string>
     */
    public function events(): array
    {
        $query = AuditEvent::query();
        $query->distinct();
        $query->orderBy('event');

        return array_values(array_filter($query->pluck('event')->all(), is_string(...)));
    }
}
