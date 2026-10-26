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

namespace Simplysmart\Admiro\Database;

use RuntimeException;
use Simplysmart\Admiro\Enums\RecordStatus;

/**
 * Zmiana stanu wiersza odrzucona: przejście spoza RecordStatus::canTransitionTo(), wyjście z migracji wiersza, który
 * nie jest w migracji, albo model bez standardowego stanu wiersza.
 */
final class RecordStatusTransitionRejected extends RuntimeException
{
    public static function between(?RecordStatus $from, RecordStatus $to): self
    {
        return new self('Record status transition rejected: ' . ($from->value ?? 'none') . ' → ' . $to->value . '.');
    }

    public static function because(string $reason): self
    {
        return new self("Record status transition rejected: {$reason}");
    }
}
