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

use Simplysmart\Admiro\Http\AccountRoutes;
use Simplysmart\Admiro\Models\AccountTask;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Queries\Tasks\AccountTaskList;
use Simplysmart\Admiro\View\Tasks\TaskItem;
use Simplysmart\Admiro\View\Tasks\TaskPanel;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Panel zadań TODO zalogowanego konta dla layoutu stref (x-admiro::zone.layout): zadania z adresami przełączenia
 * wykonania i adres zapisu komend.
 */
final readonly class AccountTaskPanel
{
    public function __construct(
        private AccountTaskList $tasks,
    ) {}

    public function for(ZoneDefinition $zone, ZoneAccount $account): TaskPanel
    {
        return new TaskPanel(
            array_map(
                static fn(AccountTask $task): TaskItem => new TaskItem($task, route($zone->routeName(AccountRoutes::TASKS_TOGGLE), ['task' => $task->token])),
                $this->tasks->forPanel($zone->name, AccountTasks::accountId($account)),
            ),
            route($zone->routeName(AccountRoutes::TASKS_STORE)),
        );
    }
}
