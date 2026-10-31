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

namespace Simplysmart\Admiro\Enums;

use Simplysmart\Admiro\Contracts\HasLabel;

/**
 * Stan zadania TODO konta (Models\AccountTask, kolumna `status`) jak w systemie ticketowym: nowe zadanie jest otwarte,
 * rozpoczęte — w toku, zrobione i zacommitowane — do sprawdzenia (z linkami do commitów), a wypchnięte (push) albo
 * zaznaczone na liście — wykonane; odznaczenie otwiera ponownie. Etykiety: `enums.task_status.<value>`.
 */
enum TaskStatus: string implements HasLabel
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Check = 'check';
    case Done = 'done';

    public function isDone(): bool
    {
        return $this === self::Done;
    }

    /**
     * Ton plakietki stanu (klasa .ss-badge--<ton>): otwarte — info, w toku — warning, do sprawdzenia — muted, wykonane — success.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Open => 'info',
            self::InProgress => 'warning',
            self::Check => 'muted',
            self::Done => 'success',
        };
    }

    public function label(): string
    {
        return text('enums.task_status.' . $this->value);
    }
}
