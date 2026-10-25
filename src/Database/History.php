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

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use LogicException;
use PDOException;
use Simplysmart\Admiro\Contracts\HistoryTriggers;
use Simplysmart\Admiro\Database\History\MysqlHistoryTriggers;
use Simplysmart\Admiro\Database\History\SqliteHistoryTriggers;

/**
 * Historia zmian tabeli w `<tabela>_history`, zapisywana triggerami bazy (docs/baza-danych.md §4),
 * więc ślad zostaje także po zmianach z konsoli SQL.
 *
 * Migracja: `app(History::class)->create('locale_units')` po utworzeniu tabeli; każda migracja
 * zmieniająca kolumny kończy się `->sync('locale_units')`, a `down()` wywołuje `->drop(...)`.
 */
final readonly class History
{
    public const string TABLE_SUFFIX = '_history';

    public function __construct(
        private DatabaseManager $connections,
    ) {}

    public static function tableFor(string $table): string
    {
        return $table . self::TABLE_SUFFIX;
    }

    public function create(string $table): void
    {
        $this->connection()->getSchemaBuilder()->create(self::tableFor($table), static function (Blueprint $history): void {
            $history->id();
            $history->unsignedBigInteger('row_id')->index();
            $history->string('action', 10);
            $history->json('old_values')->nullable();
            $history->json('new_values')->nullable();
            $history->string('actor_zone', 64)->nullable();
            $history->unsignedBigInteger('actor_id')->nullable();
            $history->string('request_id', 64)->nullable();
            $history->timestamp('created_at')->nullable();
        });

        $this->sync($table);
    }

    /**
     * Przebudowuje triggery według bieżących kolumn tabeli.
     */
    public function sync(string $table): void
    {
        $connection = $this->connection();
        $triggers = $this->triggers($connection);
        $columns = $connection->getSchemaBuilder()->getColumnListing($table);

        $this->execute($connection, $table, [...$triggers->dropStatements($table), ...$triggers->createStatements($table, self::tableFor($table), $columns)]);
    }

    public function drop(string $table): void
    {
        $connection = $this->connection();

        $this->execute($connection, $table, $this->triggers($connection)->dropStatements($table));

        $connection->getSchemaBuilder()->dropIfExists(self::tableFor($table));
    }

    /**
     * Kolumny zapisywane przez trigger AFTER INSERT (w kolejności), do porównania ze schematem tabeli.
     *
     * @return list<string>
     */
    public function trackedColumns(string $table): array
    {
        $definition = $this->triggers($this->connection())->insertTriggerDefinition($table) ?? '';
        preg_match_all("/'([^']+)', NEW\\./", $definition, $matches);

        return $matches[1];
    }

    private function connection(): Connection
    {
        return $this->connections->connection();
    }

    /**
     * DDL triggerów; nazwy tabel i kolumn są już cytowane przez gramatykę i PDO. Odmowę serwera z binlogiem bez
     * zaufania do twórców triggerów zamienia na HistoryTriggersRejected z instrukcją naprawy.
     *
     * @param list<string> $statements
     */
    private function execute(Connection $connection, string $table, array $statements): void
    {
        foreach ($statements as $statement) {
            try {
                $connection->getPdo()->exec($statement);
            } catch (PDOException $exception) {
                throw HistoryTriggersRejected::matches($exception) ? HistoryTriggersRejected::binlogNeedsTrust($table, $exception) : $exception;
            }
        }
    }

    private function triggers(Connection $connection): HistoryTriggers
    {
        return match ($connection->getDriverName()) {
            'mysql', 'mariadb' => new MysqlHistoryTriggers($connection),
            'sqlite' => new SqliteHistoryTriggers($connection),
            default => throw new LogicException("History triggers are not supported on [{$connection->getDriverName()}]."),
        };
    }
}
