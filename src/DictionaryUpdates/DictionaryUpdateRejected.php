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

namespace Simplysmart\Admiro\DictionaryUpdates;

use RuntimeException;

/**
 * Prośby o aktualizację słownika nie da się zapisać albo rozstrzygnąć (nieznany słownik, prośba już rozstrzygnięta).
 */
final class DictionaryUpdateRejected extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self('Dictionary update rejected: ' . $reason);
    }
}
