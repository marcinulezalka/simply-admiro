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

namespace Simplysmart\Admiro\View\Tasks;

/**
 * Zadania TODO konta w layoucie stref: lista pod powiadomieniami (x-admiro::app.tasks) i adres zapisu komend z pola
 * komend menu błyskawicy (admiro::search.commands).
 */
final readonly class TaskPanel
{
    /**
     * @param list<TaskItem> $items Otwarte zadania od najstarszego, pod nimi ostatnio wykonane.
     */
    public function __construct(
        public array $items,
        public string $commandUrl,
    ) {}

    public function openCount(): int
    {
        return count(array_filter($this->items, static fn(TaskItem $item): bool => ! $item->task->status->isDone()));
    }
}
