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

namespace Simplysmart\Admiro\Http\Controllers\Account;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Simplysmart\Admiro\Contracts\CurrentAccount;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Enums\QuickCommand;
use Simplysmart\Admiro\Queries\Tasks\AccountTaskList;
use Simplysmart\Admiro\Tasks\AccountTasks;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Komendy z pola komend menu błyskawicy (`todo:` zapisuje zadanie o wpisanej nazwie) i przełączenie wykonania
 * zadania z listy TODO w panelu powiadomień; po każdej zmianie powrót na poprzednią stronę z komunikatem.
 */
final readonly class TaskController
{
    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $current,
        private AccountTasks $tasks,
        private AccountTaskList $list,
        private Redirector $redirect,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string', Rule::enum(QuickCommand::class)],
            'text' => ['required', 'string', 'max:255'],
        ]);
        $text = is_string($validated['text']) ? trim($validated['text']) : '';

        match (QuickCommand::from(is_string($validated['command']) ? $validated['command'] : '')) {
            QuickCommand::Todo => $this->tasks->add($this->zone->get(), $this->current->require(), $text),
        };

        return $this->redirect->back()->with('status', text('account.task_added'));
    }

    public function toggle(string $task): RedirectResponse
    {
        $record = $this->list->find($this->zone->get()->name, AccountTasks::accountId($this->current->require()), $task)
            ?? throw new NotFoundHttpException();
        $record = $this->tasks->toggleDone($record);

        return $this->redirect->back()->with('status', text($record->status->isDone() ? 'account.task_done' : 'account.task_reopened'));
    }
}
