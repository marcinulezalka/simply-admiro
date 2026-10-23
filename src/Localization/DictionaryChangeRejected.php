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

namespace Simplysmart\Mainboard\Localization;

use RuntimeException;

/**
 * Zmiana słownika odrzucona: wyłączenie wpisu domyślnego albo języka interfejsu, stawka VAT bez procentu
 * (poza zwolnioną) albo z datami w złej kolejności.
 */
final class DictionaryChangeRejected extends RuntimeException
{
    public static function because(string $reason): self
    {
        return new self("Dictionary change rejected: {$reason}");
    }
}
