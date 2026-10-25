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

namespace Simplysmart\Mainboard\Contracts;

/**
 * Wpis słownika rozpoznawany po kodzie (ISO): seedery i odczyty szukają go w kolumnie codeColumn().
 */
interface DictionaryEntry
{
    /**
     * Kolumna z kodem wpisu, np. `alpha_code_2` kraju, `alpha_code_3` waluty, `code` języka.
     */
    public static function codeColumn(): string;
}
