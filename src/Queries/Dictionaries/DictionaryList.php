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

use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Localization\Dictionary;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\Language;
use Simplysmart\Admiro\Models\Dictionaries\TaxRate;

/**
 * Pełne słowniki (także wyłączone wpisy) dla panelu: wpisy słownika, wpis po kodzie i stawki VAT kraju.
 */
final class DictionaryList
{
    /**
     * @return Collection<int, Country>|Collection<int, Language>|Collection<int, Currency>
     */
    public function entries(Dictionary $dictionary): Collection
    {
        $model = $dictionary->model();
        $query = $model::query();
        $query->orderBy($dictionary === Dictionary::Countries ? ColumnNames::SORT_ORDER : $model::codeColumn());

        return $query->get();
    }

    public function find(Dictionary $dictionary, string $code): Country|Language|Currency|null
    {
        $model = $dictionary->model();

        return $model::query()->where($model::codeColumn(), $code)->first();
    }

    /**
     * Stawki kraju od najnowszych.
     *
     * @return Collection<int, TaxRate>
     */
    public function taxRates(Country $country): Collection
    {
        $query = TaxRate::query();
        $query->where('country_id', $country->id);
        $query->orderByDesc('valid_from');
        $query->orderBy('type');

        return $query->get();
    }

    public function taxRate(Country $country, string $token): ?TaxRate
    {
        $query = TaxRate::query();
        $query->where('country_id', $country->id);
        $query->where(ColumnNames::TOKEN, $token);

        return $query->first();
    }
}
