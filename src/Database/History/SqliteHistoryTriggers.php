<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\Database\History;

/**
 * Triggery historii SQLite (testy, środowiska bez MySQL): bez zmiennych sesji, więc aktor to NULL.
 */
final readonly class SqliteHistoryTriggers extends SqlHistoryTriggers
{
    public function insertTriggerDefinition(string $table): ?string
    {
        $definition = $this->connection->scalar(
            "SELECT sql FROM sqlite_master WHERE type = 'trigger' AND name = ?",
            [HistoryTriggerNames::Insert->triggerName($table)],
        );

        return is_string($definition) ? $definition : null;
    }

    protected function actorExpressions(): array
    {
        return ['NULL', 'NULL', 'NULL'];
    }

    protected function jsonObjectFunction(): string
    {
        return 'json_object';
    }
}
