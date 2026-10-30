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
 * Zadania TODO jednego konta strefy: lista do panelu (otwarte od najstarszego zgłoszenia, pod nimi ostatnio wykonane),
 * zadanie konta po tokenie i następny numer zgłoszenia. Zadania innych kont i stref nigdy nie trafiają do wyniku.
 */
final class AccountTaskList
{
    /** Ile ostatnio wykonanych zadań zostaje na liście pod otwartymi. */
    public const int RECENTLY_DONE = 5;

    /**
     * @return list<AccountTask>
     */
    public function forPanel(string $zone, int $accountId): array
    {
        $open = $this->of($zone, $accountId);
        $open->where('status', '!=', TaskStatus::Done->value);
        $open->orderBy('issue');

        $done = $this->of($zone, $accountId);
        $done->where('status', TaskStatus::Done->value);
        $done->orderByDesc('completed_at')->orderByDesc('issue');
        $done->limit(self::RECENTLY_DONE);

        return [...$open->get()->all(), ...$done->get()->all()];
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
