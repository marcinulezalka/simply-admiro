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
 * Nazwy triggerów historii tabeli i zdarzenia, które zapisują.
 */
enum HistoryTriggerNames: string
{
    case Insert = 'insert';
    case Update = 'update';
    case Delete = 'delete';

    public function triggerName(string $table): string
    {
        return $table . '_history_' . $this->value;
    }

    /**
     * Wiersz zapisywany w historii: po zmianie (NEW) albo przed usunięciem (OLD).
     */
    public function rowAlias(): string
    {
        return $this === self::Delete ? 'OLD' : 'NEW';
    }
}
