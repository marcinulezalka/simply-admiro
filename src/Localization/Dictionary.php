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

use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Models\Dictionaries\Currency;
use Simplysmart\Mainboard\Models\Dictionaries\Language;

/**
 * Słowniki lokalizacji zarządzane w panelu (wartość = segment URL).
 */
enum Dictionary: string
{
    case Countries = 'countries';
    case Languages = 'languages';
    case Currencies = 'currencies';

    /**
     * @return class-string<Country|Language|Currency>
     */
    public function model(): string
    {
        return match ($this) {
            self::Countries => Country::class,
            self::Languages => Language::class,
            self::Currencies => Currency::class,
        };
    }
}
