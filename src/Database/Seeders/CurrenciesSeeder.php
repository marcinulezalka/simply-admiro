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

namespace Simplysmart\Mainboard\Database\Seeders;

use Simplysmart\Mainboard\Models\Dictionaries\Currency;

/**
 * Słownik walut: każda waluta używana dziś przez kraje z listy (mapa walut ICU), nazwy z ICU.
 * Włączone od startu: waluta aplikacji (mainboard.locale.currency) i ACTIVE_CURRENCIES.
 */
final class CurrenciesSeeder extends DictionarySeeder
{
    public function run(): void
    {
        $default = strtoupper($this->config->string('mainboard.locale.currency'));
        $active = array_values(array_unique([$default, ...LocalizationData::ACTIVE_CURRENCIES]));
        $hasDefault = Currency::query()->where('is_default', true)->exists();

        foreach (array_values(array_unique([$default, ...LocalizationData::currencyCodes()])) as $code) {
            $currency = self::byCode(Currency::class, $code);
            $currency->forceFill([
                'name' => LocalizationData::currencyName($code, $this->defaultLocale()),
                'json_i18n' => $this->translations(static fn(string $locale): string => LocalizationData::currencyName($code, $locale)),
                'decimals' => LocalizationData::currencyDecimals($code),
            ]);

            if (! $currency->exists) {
                $currency->forceFill([
                    'is_active' => in_array($code, $active, true),
                    'is_default' => ! $hasDefault && $code === $default,
                    'sort_order' => self::sortOrder($code, $active),
                ]);
            }

            $currency->save();
        }
    }
}
