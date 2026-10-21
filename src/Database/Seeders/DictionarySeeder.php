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

use Illuminate\Config\Repository;
use Illuminate\Database\Seeder;
use Simplysmart\Mainboard\Models\DataModel;

/**
 * Baza seederów referencyjnych słowników: idempotentne (rekord szukany po kodzie, także usunięty),
 * zapis przez model (token, checksum, historia), a wybory administratora (włączenie, domyślny)
 * ustawiane tylko przy pierwszym utworzeniu rekordu. Wolno je uruchomić na produkcji.
 */
abstract class DictionarySeeder extends Seeder
{
    public function __construct(
        protected readonly Repository $config,
    ) {}

    /**
     * Język aplikacji: w nim zapisywana jest kolumna `name`.
     */
    protected function defaultLocale(): string
    {
        return $this->config->string('app.locale');
    }

    /**
     * Języki interfejsu z APP_LOCALES (mainboard.locale.enabled), język aplikacji zawsze.
     *
     * @return list<string>
     */
    protected function interfaceLocales(): array
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
    protected function translations(callable $translate): array
    {
        $translations = [];

        foreach ($this->interfaceLocales() as $locale) {
            $translations[$locale] = $translate($locale);
        }

        return $translations;
    }

    /**
     * Rekord słownika o kodzie (także usunięty, żeby nie powstał duplikat) albo nowy z tym kodem.
     *
     * @template TModel of DataModel
     *
     * @param class-string<TModel> $model
     *
     * @return TModel
     */
    protected static function byCode(string $model, string $code): DataModel
    {
        return $model::withTrashed()->firstOrNew(['code' => $code]);
    }

    /**
     * Pozycja na liście wyróżnionych albo OTHER_SORT_ORDER.
     *
     * @param list<string> $leading
     */
    protected static function sortOrder(string $code, array $leading): int
    {
        $position = array_search($code, $leading, true);

        return $position === false ? LocalizationData::OTHER_SORT_ORDER : $position;
    }
}
