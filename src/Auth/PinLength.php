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

namespace Simplysmart\Admiro\Auth;

use InvalidArgumentException;

/**
 * Dopuszczalna długość PIN-u strefy jako zakres cyfr, zawsze w granicach {@see self::MIN}–{@see self::MAX}.
 */
final readonly class PinLength
{
    /** Najkrótszy PIN, jaki Core przyjmuje w jakiejkolwiek strefie. */
    public const int MIN = 4;

    /** Najdłuższy PIN, jaki Core przyjmuje w jakiejkolwiek strefie. */
    public const int MAX = 16;

    /**
     * @throws InvalidArgumentException Gdy zakres wychodzi poza 4–16 albo minimum jest większe od maksimum.
     */
    public function __construct(
        public int $min,
        public int $max,
    ) {
        if ($min < self::MIN || $max > self::MAX || $min > $max) {
            throw new InvalidArgumentException('auth.pin needs ' . self::MIN . ' <= min_length <= max_length <= ' . self::MAX . ", got {$min}–{$max}.");
        }
    }

    /**
     * Każda długość, jaką Core kiedykolwiek przyjmuje: do sprawdzania wpisanego istniejącego PIN-u, który mógł
     * powstać przy innym zakresie strefy (o jego poprawności decyduje hash, nie długość).
     */
    public static function accepted(): self
    {
        return new self(self::MIN, self::MAX);
    }
}
