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

namespace Simplysmart\Admiro\View;

/**
 * Nazwy ikon flag z outhebox/blade-flags w wariancie flat (flag-icons 4:3, MIT) dla kodu `flag_icon` — jedno źródło
 * dla x-admiro::flag, Http\Controllers\FlagController i zastępczej flagi `xx` (AdmiroServiceProvider).
 */
final class FlagIcons
{
    /** Prefiks zestawu ikon blade-flags w nazwie ikony (`svg()`), bez niego w konfiguracji `blade-flags.fallback`. */
    private const string SET_PREFIX = 'flag-';

    private const string VARIANT = 'flat-country-';

    /** Zastępcza flaga „xx” w wariancie flat jako wartość `blade-flags.fallback` (nazwa w zestawie, bez prefiksu). */
    public const string FALLBACK = self::VARIANT . 'xx';

    /**
     * Pełna nazwa ikony flagi do `svg()` / Factory::svg(), np. `pl` → `flag-flat-country-pl`.
     */
    public static function name(string $code): string
    {
        return self::SET_PREFIX . self::VARIANT . $code;
    }
}
