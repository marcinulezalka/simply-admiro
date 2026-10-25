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
use LogicException;
use OverflowException;

/**
 * Pule id tabel danych (docs/baza-danych.md §2): 1–999 to sloty rekordów z seederów i providerów paczek
 * (stałe id, nadawane przez next()), rekordy użytkowników dostają id od 1000 z AUTO_INCREMENT.
 *
 * Migracja: `app(IdSlots::class)->reserve('locale_units')` po utworzeniu tabeli z Columns::base().
 */
final readonly class IdSlots
{
    public const int SEED_MAX = 999;

    public const int FIRST_USER_ID = 1000;

    private const string SQLITE_SEQUENCE = 'sqlite_sequence';

    public function __construct(
        private DatabaseManager $connections,
    ) {}

    /**
     * Ustawia licznik AUTO_INCREMENT tabeli na FIRST_USER_ID.
     */
    public function reserve(string $table): void
    {
        $connection = $this->connection();

        match ($connection->getDriverName()) {
            'mysql', 'mariadb' => $connection->statement('ALTER TABLE ' . $connection->getQueryGrammar()->wrapTable($table) . ' AUTO_INCREMENT = ' . self::FIRST_USER_ID),
            'sqlite' => $this->reserveInSqlite($connection, $table),
            default => throw new LogicException("Id slots are not supported on [{$connection->getDriverName()}]."),
        };
    }

    /**
     * Kolejny wolny slot seedera (najwyższe id poniżej FIRST_USER_ID plus jeden, także po usuniętych rekordach).
     *
     * @throws OverflowException Gdy pula 1–999 jest pełna.
     */
    public function next(string $table): int
    {
        $highest = $this->connection()->table($table)->where(ColumnNames::ID, '<', self::FIRST_USER_ID)->max(ColumnNames::ID);
        $next = (is_numeric($highest) ? (int) $highest : 0) + 1;

        if ($next > self::SEED_MAX) {
            throw new OverflowException("Seed id slots of [{$table}] are full (1–" . self::SEED_MAX . ').');
        }

        return $next;
    }

    private function reserveInSqlite(Connection $connection, string $table): bool
    {
        $connection->table(self::SQLITE_SEQUENCE)->where('name', $table)->delete();

        return $connection->table(self::SQLITE_SEQUENCE)->insert(['name' => $table, 'seq' => self::SEED_MAX]);
    }

    private function connection(): Connection
    {
        return $this->connections->connection();
    }
}
