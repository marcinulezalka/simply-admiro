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

namespace Simplysmart\Admiro\Queries\Dictionaries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Database\CodeColumns;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\Language;

/**
 * Wyszukiwanie w słownikach (także wyłączonych wpisów) bez rozróżniania wielkości liter: po kodzie, nazwie
 * i tłumaczeniach (`json_i18n`). Języki dodatkowo po nazwie własnej i po krajach, w których są urzędowe, więc
 * „polska” znajduje język polski. Pusty tekst daje pustą listę.
 */
final class DictionarySearch
{
    /**
     * @return Collection<int, Language>
     */
    public function languages(string $term, ?int $limit = null): Collection
    {
        $needle = self::needle($term);

        if ($needle === null) {
            return new Collection();
        }

        $query = Language::query();
        $query->where(static function (Builder $match) use ($needle): void {
            self::matchEntry($match, Language::CODE, $needle);
            $match->orWhereRaw('LOWER(native_name) LIKE ?', [$needle]);
            $match->orWhereHas('countries', static function (Builder $country) use ($needle): void {
                $country->where(static fn(Builder $countryMatch) => self::matchEntry($countryMatch, CodeColumns::ALPHA_2, $needle));
            });
        });

        return self::ordered($query, Language::codeColumn(), $limit);
    }

    /**
     * @return Collection<int, Country>
     */
    public function countries(string $term, int $limit): Collection
    {
        $needle = self::needle($term);

        if ($needle === null) {
            return new Collection();
        }

        $query = Country::query();
        $query->where(static function (Builder $match) use ($needle): void {
            self::matchEntry($match, CodeColumns::ALPHA_2, $needle);
            $match->orWhereRaw('LOWER(' . CodeColumns::ALPHA_3 . ') LIKE ?', [$needle]);
        });

        return self::ordered($query, ColumnNames::SORT_ORDER, $limit);
    }

    /**
     * @return Collection<int, Currency>
     */
    public function currencies(string $term, int $limit): Collection
    {
        $needle = self::needle($term);

        if ($needle === null) {
            return new Collection();
        }

        $query = Currency::query();
        $query->where(static fn(Builder $match) => self::matchEntry($match, CodeColumns::ALPHA_3, $needle));

        return self::ordered($query, Currency::codeColumn(), $limit);
    }

    /**
     * Wzorzec LIKE z tekstu (małe litery, bez `%` i `_`, które LIKE traktuje jako symbole) albo null dla pustego tekstu.
     */
    private static function needle(string $term): ?string
    {
        $term = mb_strtolower(trim(str_replace(['%', '_'], ' ', $term)));

        return $term === '' ? null : '%' . $term . '%';
    }

    /**
     * @param Builder<covariant Model> $query
     * @param literal-string $codeColumn Kolumna kodu słownika (stała z kodu, nigdy z żądania).
     */
    private static function matchEntry(Builder $query, string $codeColumn, string $needle): void
    {
        $query->whereRaw('LOWER(' . $codeColumn . ') LIKE ?', [$needle])
            ->orWhereRaw('LOWER(' . ColumnNames::NAME . ') LIKE ?', [$needle])
            ->orWhereRaw('LOWER(' . ColumnNames::JSON_I18N . ') LIKE ?', [$needle]);
    }

    /**
     * @template TModel of Model
     *
     * @param Builder<TModel> $query
     *
     * @return Collection<int, TModel>
     */
    private static function ordered(Builder $query, string $column, ?int $limit): Collection
    {
        $query->orderByDesc(ColumnNames::IS_ACTIVE);
        $query->orderBy($column);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
