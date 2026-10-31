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

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Illuminate\View\Compilers\BladeCompiler;
use Simplysmart\Admiro\Contracts\CurrentAccount;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Enums\QuickCommand;
use Simplysmart\Admiro\Queries\Tasks\AccountTaskList;
use Simplysmart\Admiro\Tasks\AccountTaskPanel;
use Simplysmart\Admiro\Tasks\AccountTasks;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Edycja zadania z modalu na liście TODO (nazwa, tytuł, opis) wraca na poprzednią stronę z komunikatem.
 * Komendy z pola komend menu błyskawicy (`todo:` zapisuje zadanie o wpisanej nazwie i adresie strony z nagłówka
 * `Referer` — Referrer-Policy `same-origin` przekazuje go w całości) i przełączenie wykonania
 * zadania z listy TODO w panelu powiadomień; po każdej zmianie powrót na poprzednią stronę z komunikatem. Komenda
 * wysłana skryptem (js/command-box.js, `Accept: application/json`) dostaje zamiast przekierowania JSON z komunikatem
 * i odświeżoną listą zadań (x-admiro::app.tasks; `lists` — lista w każdym zakresie: panel powiadomień i sidebar wiki), więc pole zostaje otwarte na kolejne zadanie.
 */
final readonly class TaskController
{
    private const int MAX_SOURCE_URL = 2048;

    public function __construct(
        private CurrentZone $zone,
        private CurrentAccount $current,
        private AccountTasks $tasks,
        private AccountTaskList $list,
        private AccountTaskPanel $panel,
        private Redirector $redirect,
    ) {}

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string', Rule::enum(QuickCommand::class)],
            'text' => ['required', 'string', 'max:255'],
        ]);
        $text = is_string($validated['text']) ? trim($validated['text']) : '';

        $zone = $this->zone->get();
        $account = $this->current->require();

        match (QuickCommand::from(is_string($validated['command']) ? $validated['command'] : '')) {
            QuickCommand::Todo => $this->tasks->add($zone, $account, $text, $this->sourceUrl($request)),
        };

        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' => text('account.task_added'),
                'tasks' => $panel = BladeCompiler::render('<x-admiro::app.tasks :tasks="$tasks" />', ['tasks' => $tasks = $this->panel->for($zone, $account)]),
                'lists' => [
                    'panel' => $panel,
                    'sidebar' => BladeCompiler::render('<x-admiro::app.tasks :tasks="$tasks" scope="sidebar" />', ['tasks' => $tasks]),
                ],
            ]);
        }

        return $this->redirect->back()->with('status', text('account.task_added'));
    }

    public function toggle(string $task): RedirectResponse
    {
        $record = $this->list->find($this->zone->get()->name, AccountTasks::accountId($this->current->require()), $task)
            ?? throw new NotFoundHttpException();
        $record = $this->tasks->toggleDone($record);

        return $this->redirect->back()->with('status', text($record->status->isDone() ? 'account.task_done' : 'account.task_reopened'));
    }

    public function update(Request $request, string $task): RedirectResponse
    {
        $record = $this->list->find($this->zone->get()->name, AccountTasks::accountId($this->current->require()), $task)
            ?? throw new NotFoundHttpException();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->tasks->edit($record, self::text($validated['name']), self::text($validated['title'] ?? null), self::text($validated['description'] ?? null));

        return $this->redirect->back()->with('status', text('account.task_updated'));
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * Adres strony, z której wysłano komendę: nagłówek `Referer`, gdy nie jest dłuższy niż MAX_SOURCE_URL.
     */
    private function sourceUrl(Request $request): ?string
    {
        $url = $request->headers->get('referer');

        return is_string($url) && $url !== '' && strlen($url) <= self::MAX_SOURCE_URL ? $url : null;
    }
}
