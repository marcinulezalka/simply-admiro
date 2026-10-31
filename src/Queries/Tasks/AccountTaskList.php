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
 * Zadania TODO jednego konta strefy: lista do panelu (niezrealizowane — otwarte i w toku — od najstarszego zgłoszenia;
 * zrealizowane są ukryte),
 * zadanie konta po tokenie i następny numer zgłoszenia. Zadania innych kont i stref nigdy nie trafiają do wyniku.
 */
final class AccountTaskList
{
    /**
     * @return list<AccountTask>
     */
    public function forPanel(string $zone, int $accountId): array
    {
        $query = $this->of($zone, $accountId);
        $query->where('status', '!=', TaskStatus::Done->value);
        $query->orderBy('issue');

        return array_values($query->get()->all());
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
