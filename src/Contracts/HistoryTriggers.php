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

namespace Simplysmart\Admiro\Contracts;

/**
 * Dialekt triggerów historii zmian jednego silnika bazy (Database\History).
 */
interface HistoryTriggers
{
    /**
     * Polecenia tworzące triggery AFTER INSERT/UPDATE/DELETE, które zapisują cały wiersz jako JSON.
     *
     * @param list<string> $columns Kolumny tabeli w kolejności schematu.
     *
     * @return list<string>
     */
    public function createStatements(string $table, string $historyTable, array $columns): array;

    /**
     * @return list<string>
     */
    public function dropStatements(string $table): array;

    /**
     * SQL triggera AFTER INSERT tabeli albo null, gdy go nie ma.
     */
    public function insertTriggerDefinition(string $table): ?string;
}
