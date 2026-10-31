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

use Simplysmart\Admiro\Enums\TaskStatus;

/**
 * Zadania TODO konta w layoucie stref: lista pod powiadomieniami (x-admiro::app.tasks) w dwóch obszarach —
 * niezrealizowane (otwarte i w toku, pending()) oraz do sprawdzenia i wdrożone (delivered()) — i adres zapisu komend
 * z pola komend menu błyskawicy (admiro::search.commands).
 */
final readonly class TaskPanel
{
    /**
     * @param list<TaskItem> $items Niezrealizowane od najstarszego, za nimi do sprawdzenia i ostatnio wykonane.
     */
    public function __construct(
        public array $items,
        public string $commandUrl,
    ) {}

    public function openCount(): int
    {
        return count($this->pending());
    }

    /**
     * Niezrealizowane: otwarte i w toku.
     *
     * @return list<TaskItem>
     */
    public function pending(): array
    {
        return array_values(array_filter($this->items, static fn(TaskItem $item): bool => ! self::isDelivered($item)));
    }

    /**
     * Do sprawdzenia (zrobione i zacommitowane) i wdrożone (wykonane).
     *
     * @return list<TaskItem>
     */
    public function delivered(): array
    {
        return array_values(array_filter($this->items, self::isDelivered(...)));
    }

    private static function isDelivered(TaskItem $item): bool
    {
        return in_array($item->task->status, [TaskStatus::Check, TaskStatus::Done], true);
    }
}
