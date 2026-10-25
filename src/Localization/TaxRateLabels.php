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

use NumberFormatter;

/**
 * Etykieta stawki VAT („23%”, „5,5%”, „zw” dla zwolnionej) w języku aplikacji i tłumaczenia do `json_i18n`.
 */
final readonly class TaxRateLabels
{
    public function __construct(
        private DictionaryLocales $locales,
    ) {}

    /**
     * @return array{name: string, json_i18n: array<string, string>}
     */
    public function for(float|int|string|null $rate): array
    {
        $label = static fn(string $locale): string => $rate === null
            ? text('enums.tax_rate_exempt_short', [], $locale)
            : self::percent((float) $rate, $locale);

        return ['name' => $label($this->locales->defaultLocale()), 'json_i18n' => $this->locales->translations($label)];
    }

    public static function percent(float|int $rate, string $locale): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

        return $formatter->format($rate) . '%';
    }
}
