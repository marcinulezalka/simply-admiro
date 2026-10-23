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
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Models\AuditEvent;

/**
 * Dziennik zdarzeń dla paneli: strona wpisów od najnowszych z filtrami (strefa, zdarzenie, zakres dat)
 * i lista nazw zdarzeń do filtra.
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
