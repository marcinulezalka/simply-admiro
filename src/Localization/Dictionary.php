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

namespace Simplysmart\Admiro\Localization;

use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\Language;

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
