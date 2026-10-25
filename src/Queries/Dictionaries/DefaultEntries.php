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

use Illuminate\Config\Repository;
use Simplysmart\Admiro\Contracts\DictionaryEntry;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\DataModel;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\Language;

/**
 * Kraj, język i waluta aplikacji: wpis słownika oznaczony `is_default`, a gdy go nie ma — wpis o kodzie
 * z configu (admiro.localization.country, app.locale, admiro.locale.currency); null bez słownika.
 */
final readonly class DefaultEntries
{
    public function __construct(
        private Repository $config,
    ) {}

    public function country(): ?Country
    {
        return $this->find(Country::class, strtoupper($this->configured('admiro.localization.country')));
    }

    public function language(): ?Language
    {
        return $this->find(Language::class, strtolower($this->configured('app.locale')));
    }

    public function currency(): ?Currency
    {
        return $this->find(Currency::class, strtoupper($this->configured('admiro.locale.currency')));
    }

    /**
     * @template TModel of DataModel&DictionaryEntry
     *
     * @param class-string<TModel> $model
     *
     * @return TModel|null
     */
    private function find(string $model, string $code): ?DataModel
    {
        return $model::query()->where(ColumnNames::IS_DEFAULT, true)->first()
            ?? $model::query()->where($model::codeColumn(), $code)->first();
    }

    private function configured(string $key): string
    {
        return $this->config->string($key);
    }
}
