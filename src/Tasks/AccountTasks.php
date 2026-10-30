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

namespace Simplysmart\Admiro\Tasks;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use LogicException;
use Simplysmart\Admiro\Auth\AccountKey;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\TaskStatus;
use Simplysmart\Admiro\Models\AccountTask;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Queries\Tasks\AccountTaskList;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Zapis zadań TODO konta strefy: nowe zadanie z komendy `todo:` (otwarte, z kolejnym numerem zgłoszenia konta)
 * i przełączenie wykonania z listy zadań (wykonane ↔ otwarte, z chwilą wykonania).
 */
final readonly class AccountTasks
{
    public function __construct(
        private AccountTaskList $tasks,
        private ConnectionInterface $db,
    ) {}

    public function add(ZoneDefinition $zone, ZoneAccount $account, string $name): AccountTask
    {
        $accountId = self::accountId($account);

        return $this->db->transaction(function () use ($zone, $accountId, $name): AccountTask {
            $task = new AccountTask();
            $task->forceFill([
                ColumnNames::NAME => $name,
                ColumnNames::IS_ACTIVE => true,
                'zone' => $zone->name,
                'account_id' => $accountId,
                'issue' => $this->tasks->nextIssue($zone->name, $accountId),
                'status' => TaskStatus::Open,
            ])->save();

            return $task;
        });
    }

    /**
     * Wykonane zadanie wraca do otwartych, każde inne staje się wykonane.
     */
    public function toggleDone(AccountTask $task): AccountTask
    {
        $done = ! $task->status->isDone();
        $task->forceFill([
            'status' => $done ? TaskStatus::Done : TaskStatus::Open,
            'completed_at' => $done ? Carbon::now() : null,
        ])->save();

        return $task;
    }

    /**
     * Identyfikator konta w tabeli kont jego strefy.
     *
     * @throws LogicException Gdy konto nie ma identyfikatora liczbowego.
     */
    public static function accountId(ZoneAccount $account): int
    {
        $key = AccountKey::of($account);

        return is_int($key) ? $key : throw new LogicException('Account tasks need an integer account identifier.');
    }
}
