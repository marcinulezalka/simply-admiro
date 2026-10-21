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

use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Models\Dictionaries\CountryLanguage;
use Simplysmart\Mainboard\Models\Dictionaries\Currency;
use Simplysmart\Mainboard\Models\Dictionaries\Language;

/**
 * Słownik krajów: nazwy z ICU, numer kierunkowy, członkostwo w UE; kraj aplikacji
 * (mainboard.localization.country) i LEADING_COUNTRIES na początku list. Walutę i języki urzędowe
 * (najczęściej używany jako główny) dopisuje tylko krajom, które ich jeszcze nie mają, i tylko gdy
 * słowniki walut i języków je zawierają — poprawki administratora zostają.
 */
final class CountriesSeeder extends DictionarySeeder
{
    public function run(): void
    {
        $default = strtoupper($this->config->string('mainboard.localization.country'));
        $leading = array_values(array_unique([$default, ...LocalizationData::LEADING_COUNTRIES]));
        $hasDefault = Country::query()->where('is_default', true)->exists();
        $currencies = Currency::query()->get()->mapWithKeys(static fn(Currency $currency): array => [$currency->code => $currency->id])->all();
        $languages = Language::query()->get()->mapWithKeys(static fn(Language $language): array => [$language->code => $language->id])->all();

        foreach (LocalizationData::countryCodes() as $code) {
            $country = self::byCode(Country::class, $code);
            $country->forceFill([
                'name' => LocalizationData::countryName($code, $this->defaultLocale()),
                'json_i18n' => $this->translations(static fn(string $locale): string => LocalizationData::countryName($code, $locale)),
                'dial_code' => LocalizationData::dialCode($code),
                'is_eu' => in_array($code, LocalizationData::EU, true),
            ]);

            if (! $country->exists) {
                $country->forceFill([
                    'is_active' => true,
                    'is_default' => ! $hasDefault && $code === $default,
                    'sort_order' => self::sortOrder($code, $leading),
                ]);
            }

            $currency = LocalizationData::currencyOf($code);

            if ($country->currency_id === null && $currency !== null && isset($currencies[$currency])) {
                $country->forceFill(['currency_id' => $currencies[$currency]]);
            }

            $country->save();

            if (! $country->languages()->exists()) {
                $official = array_values(array_filter(LocalizationData::officialLanguagesOf($code), static fn(string $language): bool => isset($languages[$language])));
                $links = [];

                foreach ($official as $index => $language) {
                    $links[$languages[$language]] = [CountryLanguage::IS_PRIMARY => $index === 0];
                }

                $country->languages()->sync($links);
            }
        }
    }
}
