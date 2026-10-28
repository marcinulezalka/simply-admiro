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

namespace Simplysmart\Admiro\Queries\Cookies;

use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Enums\ConsentDecision;
use Simplysmart\Admiro\Models\CookieConsent;

/**
 * Liczba zgód na cookies według decyzji z ostatnich dni (bez identyfikatorów), do statystyki panelu polityki cookies.
 */
final class ConsentCounts
{
    /**
     * @return array<string, int> Decyzja → liczba zgód z ostatnich `$days` dni (każda decyzja, także 0).
     */
    public function lastDays(int $days): array
    {
        $query = CookieConsent::query();
        $query->where('recorded_at', '>=', Carbon::now()->subDays($days));
        $query->selectRaw('decision, count(*) as total');
        $query->groupBy('decision');
        $counts = array_fill_keys(array_map(static fn(ConsentDecision $decision): string => $decision->value, ConsentDecision::cases()), 0);

        foreach ($query->toBase()->get() as $row) {
            if (is_string($row->decision ?? null) && isset($counts[$row->decision]) && is_numeric($row->total ?? null)) {
                $counts[$row->decision] = (int) $row->total;
            }
        }

        return $counts;
    }
}
