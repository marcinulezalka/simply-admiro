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

namespace Simplysmart\Mainboard\Queries\Cookies;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Enums\ConsentDecision;
use Simplysmart\Mainboard\Models\CookieConsent;
use Simplysmart\Mainboard\Models\CookiePolicy;

/**
 * Wersje polityki cookies: bieżąca (najwyższy opublikowany numer), szkic, opublikowane od najnowszej i liczba zgód
 * według decyzji z ostatnich dni (bez identyfikatorów).
 */
final class CookiePolicyVersions
{
    public function current(): ?CookiePolicy
    {
        $query = CookiePolicy::query();
        $query->whereNotNull('version');
        $query->orderByDesc('version');

        return $query->first();
    }

    public function draft(): ?CookiePolicy
    {
        $query = CookiePolicy::query();
        $query->whereNull('version');

        return $query->first();
    }

    /**
     * @return Collection<int, CookiePolicy>
     */
    public function published(): Collection
    {
        $query = CookiePolicy::query();
        $query->whereNotNull('version');
        $query->orderByDesc('version');

        return $query->get();
    }

    /**
     * @return array<string, int> Decyzja → liczba zgód z ostatnich `$days` dni (każda decyzja, także 0).
     */
    public function consentCounts(int $days): array
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
