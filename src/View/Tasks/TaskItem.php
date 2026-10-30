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

use Simplysmart\Admiro\Models\AccountTask;

/**
 * Zadanie na liście TODO panelu powiadomień: zadanie konta i adres przełączenia wykonania (PATCH).
 */
final readonly class TaskItem
{
    public function __construct(
        public AccountTask $task,
        public string $toggleUrl,
    ) {}
}
