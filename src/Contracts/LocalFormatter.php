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

use DateTimeInterface;

/**
 * Formatowanie regionalne według języka: kwoty, liczby, daty, nazwy dni i miesięcy.
 *
 * Bez podanego $locale używa bieżącego języka aplikacji.
 */
interface LocalFormatter
{
    /**
     * Kwota z walutą, np. "1 234,50 zł" (pl) albo "PLN 1,234.50" (en).
     *
     * @param  string|null  $currency  kod ISO 4217; null = waluta domyślna z config('mainboard.locale.currency')
     */
    public function money(int|float $amount, ?string $currency = null, ?string $locale = null): string;

    /**
     * Liczba z separatorami tysięcy i dziesiętnymi właściwymi dla języka.
     */
    public function number(int|float $number, int $precision = 0, ?string $locale = null): string;

    /**
     * Data w formacie ISO Carbona (isoFormat), domyślnie "L": 20.10.2026 (pl), 10/20/2026 (en).
     */
    public function date(DateTimeInterface|string $date, ?string $format = null, ?string $locale = null): string;

    /**
     * Nazwa dnia tygodnia; liczba to dzień ISO 1–7 (1 = poniedziałek).
     */
    public function day(int|DateTimeInterface $day, ?string $format = null, ?string $locale = null): string;

    /**
     * Nazwa miesiąca w mianowniku; liczba to miesiąc 1–12.
     */
    public function month(int|DateTimeInterface $month, ?string $format = null, ?string $locale = null): string;
}
