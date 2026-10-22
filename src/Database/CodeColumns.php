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

namespace Simplysmart\Mainboard\Database;

/**
 * Nazwy kolumn kodów ISO w słownikach (docs/baza-danych.md §14): jedna konwencja dla każdego standardu.
 */
final class CodeColumns
{
    /**
     * Kod dwuliterowy wielkimi literami (ISO 3166-1 alpha-2: `PL`).
     */
    public const string ALPHA_2 = 'alpha_code_2';

    /**
     * Kod trzyliterowy wielkimi literami (ISO 3166-1 alpha-3: `POL`, ISO 4217: `PLN`).
     */
    public const string ALPHA_3 = 'alpha_code_3';

    /**
     * Kod numeryczny jako trzy cyfry z zerami wiodącymi (ISO 3166-1 numeric: `616`, ISO 4217: `985`).
     */
    public const string NUMERIC = 'numeric_code';
}
