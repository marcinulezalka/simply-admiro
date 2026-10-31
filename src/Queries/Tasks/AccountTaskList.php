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

namespace Simplysmart\Admiro\Queries\Tasks;

use Illuminate\Database\Eloquent\Builder;
use Simplysmart\Admiro\Enums\TaskStatus;
use Simplysmart\Admiro\Models\AccountTask;

/**
 * Zadania TODO jednego konta strefy: lista do panelu (niezrealizowane — otwarte i w toku — od najstarszego zgłoszenia,
 * za nimi do sprawdzenia od najstarszego i ostatnio wykonane — RECENTLY_DONE),
 * zadanie konta po tokenie i następny numer zgłoszenia. Zadania innych kont i stref nigdy nie trafiają do wyniku.
 */
final class AccountTaskList
{
    /** Ile ostatnio wykonanych zadań pokazuje obszar „do sprawdzenia i wdrożone”. */
    public const int RECENTLY_DONE = 20;

    /**
     * @return list<AccountTask>
     */
    public function forPanel(string $zone, int $accountId): array
    {
        $unfinished = $this->of($zone, $accountId);
        $unfinished->where('status', '!=', TaskStatus::Done->value);
        $unfinished->orderByRaw('status = ?', [TaskStatus::Check->value])->orderBy('issue');

        $done = $this->of($zone, $accountId);
        $done->where('status', TaskStatus::Done->value);
        $done->orderByDesc('completed_at')->orderByDesc('issue');
        $done->limit(self::RECENTLY_DONE);

        return [...array_values($unfinished->get()->all()), ...array_values($done->get()->all())];
    }

    public function find(string $zone, int $accountId, string $token): ?AccountTask
    {
        $query = $this->of($zone, $accountId);
        $query->where('token', $token);

        return $query->first();
    }

    /**
     * Kolejny numer zgłoszenia konta (także po usuniętych zadaniach, więc numer się nie powtarza).
     */
    public function nextIssue(string $zone, int $accountId): int
    {
        $query = AccountTask::withTrashed();
        $query->where('zone', $zone)->where('account_id', $accountId);
        $last = $query->lockForUpdate()->max('issue');

        return (is_numeric($last) ? (int) $last : 0) + 1;
    }

    /**
     * @return Builder<AccountTask>
     */
    private function of(string $zone, int $accountId): Builder
    {
        $query = AccountTask::query();
        $query->where('zone', $zone)->where('account_id', $accountId);

        return $query;
    }
}
