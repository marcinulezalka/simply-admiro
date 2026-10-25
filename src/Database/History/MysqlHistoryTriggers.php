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
 * Triggery historii MySQL: aktor ze zmiennych sesji połączenia ustawianych przez HistoryActor.
 */
final readonly class MysqlHistoryTriggers extends SqlHistoryTriggers
{
    public function insertTriggerDefinition(string $table): ?string
    {
        $definition = $this->connection->scalar(
            'SELECT ACTION_STATEMENT FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME = ?',
            [HistoryTriggerNames::Insert->triggerName($table)],
        );

        return is_string($definition) ? $definition : null;
    }

    protected function actorExpressions(): array
    {
        return ['@actor_zone', '@actor_id', '@request_id'];
    }

    protected function jsonObjectFunction(): string
    {
        return 'JSON_OBJECT';
    }
}
