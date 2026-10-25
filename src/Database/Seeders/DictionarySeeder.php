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

use Illuminate\Config\Repository;
use Illuminate\Database\Seeder;
use Simplysmart\Admiro\Contracts\DictionaryEntry;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Database\IdSlots;
use Simplysmart\Admiro\Localization\DictionaryLocales;
use Simplysmart\Admiro\Models\DataModel;

/**
 * Baza seederów referencyjnych słowników: idempotentne (rekord szukany po kodzie, także usunięty),
 * zapis przez model (token, checksum, historia), a wybory administratora (włączenie, domyślny)
 * ustawiane tylko przy pierwszym utworzeniu rekordu. Nowe rekordy dostają id z puli seederów (IdSlots, 1–999).
 * Wolno je uruchomić na produkcji. Języki nazw i tłumaczeń daje Localization\DictionaryLocales.
 */
abstract class DictionarySeeder extends Seeder
{
    public function __construct(
        protected readonly Repository $config,
        private readonly IdSlots $idSlots,
        private readonly DictionaryLocales $locales,
    ) {}

    protected function defaultLocale(): string
    {
        return $this->locales->defaultLocale();
    }

    /**
     * @return list<string>
     */
    protected function interfaceLocales(): array
    {
        return $this->locales->interfaceLocales();
    }

    /**
     * @param callable(string): string $translate
     *
     * @return array<string, string>
     */
    protected function translations(callable $translate): array
    {
        return $this->locales->translations($translate);
    }

    /**
     * Rekord słownika o kodzie (także usunięty, żeby nie powstał duplikat) albo nowy z tym kodem i id z puli seederów.
     *
     * @template TModel of DataModel&DictionaryEntry
     *
     * @param class-string<TModel> $model
     *
     * @return TModel
     */
    protected function byCode(string $model, string $code): DataModel
    {
        return $this->withSeedId($model::withTrashed()->firstOrNew([$model::codeColumn() => $code]));
    }

    /**
     * Nowemu rekordowi nadaje kolejne id z puli seederów; istniejący zostaje bez zmian.
     *
     * @template TModel of DataModel
     *
     * @param TModel $record
     *
     * @return TModel
     */
    protected function withSeedId(DataModel $record): DataModel
    {
        if (! $record->exists) {
            $record->setAttribute(ColumnNames::ID, $this->idSlots->next($record->getTable()));
        }

        return $record;
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
