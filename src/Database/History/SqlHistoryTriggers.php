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

namespace Simplysmart\Admiro\Database\History;

use Illuminate\Database\Connection;
use Simplysmart\Admiro\Contracts\HistoryTriggers;

/**
 * Wspólna budowa triggerów historii; dialekt podaje funkcję JSON, aktora i odczyt definicji.
 */
abstract readonly class SqlHistoryTriggers implements HistoryTriggers
{
    public function __construct(
        protected Connection $connection,
    ) {}

    /**
     * Wyrażenia kolumn aktora: strefa, id konta, id żądania.
     *
     * @return array{0: string, 1: string, 2: string}
     */
    abstract protected function actorExpressions(): array;

    abstract protected function jsonObjectFunction(): string;

    public function createStatements(string $table, string $historyTable, array $columns): array
    {
        $statements = [];

        foreach (HistoryTriggerNames::cases() as $event) {
            $new = $event === HistoryTriggerNames::Delete ? 'NULL' : $this->rowJson('NEW', $columns);
            $old = $event === HistoryTriggerNames::Insert ? 'NULL' : $this->rowJson('OLD', $columns);
            [$zone, $actor, $request] = $this->actorExpressions();

            $statements[] = sprintf(
                'CREATE TRIGGER %s AFTER %s ON %s FOR EACH ROW BEGIN INSERT INTO %s (row_id, action, old_values, new_values, actor_zone, actor_id, request_id, created_at) VALUES (%s.id, %s, %s, %s, %s, %s, %s, CURRENT_TIMESTAMP); END',
                $this->wrap($event->triggerName($table)),
                strtoupper($event->value),
                $this->wrap($table),
                $this->wrap($historyTable),
                $event->rowAlias(),
                $this->connection->getPdo()->quote($event->value),
                $old,
                $new,
                $zone,
                $actor,
                $request,
            );
        }

        return $statements;
    }

    public function dropStatements(string $table): array
    {
        return array_map(
            fn(HistoryTriggerNames $event): string => 'DROP TRIGGER IF EXISTS ' . $this->wrap($event->triggerName($table)),
            HistoryTriggerNames::cases(),
        );
    }

    protected function wrap(string $identifier): string
    {
        return $this->connection->getQueryGrammar()->wrap($identifier);
    }

    /**
     * @param list<string> $columns
     */
    private function rowJson(string $alias, array $columns): string
    {
        $pairs = array_map(
            fn(string $column): string => $this->connection->getPdo()->quote($column) . ', ' . $alias . '.' . $this->wrap($column),
            $columns,
        );

        return $this->jsonObjectFunction() . '(' . implode(', ', $pairs) . ')';
    }
}
