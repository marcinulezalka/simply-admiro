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

namespace Simplysmart\Admiro\Database\Seeders;

use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Dictionaries\Language;

/**
 * Słownik języków: języki urzędowe krajów z listy (dane ICU) z nazwą w językach interfejsu
 * i nazwą własną; ikonę flagi (LocalizationData::languageFlagIcons()) dopisuje tylko wpisom bez niej, a flagi
 * wszystkich krajów języka (Language::FLAG_ICONS w `json_attributes`) odświeża przy każdym uruchomieniu.
 * Od startu: język aplikacji domyślny, języki z APP_LOCALES jako języki interfejsu i tylko one włączone —
 * pozostałe administrator włącza w onboardingu (krok „Języki”).
 */
final class LanguagesSeeder extends DictionarySeeder
{
    public function run(): void
    {
        $default = $this->defaultLocale();
        $interface = $this->interfaceLocales();
        $active = $interface;
        $hasDefault = Language::query()->where(ColumnNames::IS_DEFAULT, true)->exists();

        $codes = array_values(array_unique([...$active, ...LocalizationData::languageCodes()]));
        $flagIcons = LocalizationData::languageFlagIcons($codes);
        $countryFlags = LocalizationData::languageCountryFlagIcons($codes);

        foreach ($codes as $code) {
            $language = $this->byCode(Language::class, $code);
            $language->forceFill([
                'name' => LocalizationData::languageName($code, $default),
                'native_name' => LocalizationData::languageName($code, $code),
                'json_i18n' => $this->translations(static fn(string $locale): string => LocalizationData::languageName($code, $locale)),
            ]);

            if ($language->flag_icon === null) {
                $language->forceFill(['flag_icon' => $flagIcons[$code] ?? null]);
            }

            $language->forceFill([ColumnNames::JSON_ATTRIBUTES => [...($language->json_attributes ?? []), Language::FLAG_ICONS => $countryFlags[$code] ?? []]]);

            if (! $language->exists) {
                $language->forceFill([
                    'is_active' => in_array($code, $active, true),
                    'is_interface' => in_array($code, $interface, true),
                    'is_default' => ! $hasDefault && $code === $default,
                    'sort_order' => self::sortOrder($code, $active),
                ]);
            }

            $language->save();
        }
    }
}
