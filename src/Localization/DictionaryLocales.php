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

use Illuminate\Config\Repository;

/**
 * Języki nazw wpisów słowników: język aplikacji (kolumna `name`) i języki z `APP_LOCALES` (tłumaczenia w `json_i18n`).
 * Wspólne dla seederów i akcji tworzących wpisy słowników.
 */
final readonly class DictionaryLocales
{
    public function __construct(
        private Repository $config,
    ) {}

    public function defaultLocale(): string
    {
        return $this->config->string('app.locale');
    }

    /**
     * Języki interfejsu z APP_LOCALES (mainboard.locale.enabled), język aplikacji zawsze.
     *
     * @return list<string>
     */
    public function interfaceLocales(): array
    {
        $enabled = array_filter($this->config->array('mainboard.locale.enabled', []), is_string(...));

        return array_values(array_unique([$this->defaultLocale(), ...$enabled]));
    }

    /**
     * Tłumaczenia do `json_i18n`: język => tekst, dla każdego języka interfejsu.
     *
     * @param callable(string): string $translate
     *
     * @return array<string, string>
     */
    public function translations(callable $translate): array
    {
        $translations = [];

        foreach ($this->interfaceLocales() as $locale) {
            $translations[$locale] = $translate($locale);
        }

        return $translations;
    }
}
