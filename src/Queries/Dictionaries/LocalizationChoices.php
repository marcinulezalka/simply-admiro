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
use Simplysmart\Admiro\Database\CodeColumns;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Localization\LocaleRouting;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Currency;
use Simplysmart\Admiro\Models\Dictionaries\Language;

/**
 * Wybory w ustawieniach lokalizacji: aktywne kraje i waluty, języki z `APP_LOCALES` (kandydaci na języki
 * interfejsu i język domyślny) oraz wpis po kodzie z formularza.
 */
final readonly class LocalizationChoices
{
    public function __construct(
        private LocaleRouting $routing,
    ) {}

    /**
     * @return Collection<int, Country>
     */
    public function countries(): Collection
    {
        $query = Country::query();
        $query->where(ColumnNames::IS_ACTIVE, true);
        $query->orderBy(ColumnNames::SORT_ORDER);

        return $query->get();
    }

    /**
     * @return Collection<int, Currency>
     */
    public function currencies(): Collection
    {
        $query = Currency::query();
        $query->where(ColumnNames::IS_ACTIVE, true);
        $query->orderBy(CodeColumns::ALPHA_3);

        return $query->get();
    }

    /**
     * @return Collection<int, Language>
     */
    public function interfaceCandidates(): Collection
    {
        $query = Language::query();
        $query->whereIn(Language::CODE, $this->routing->enabled());
        $query->orderBy(Language::CODE);

        return $query->get();
    }

    public function country(string $code): ?Country
    {
        return Country::query()->where(CodeColumns::ALPHA_2, $code)->first();
    }

    public function currency(string $code): ?Currency
    {
        return Currency::query()->where(CodeColumns::ALPHA_3, $code)->first();
    }

    public function language(string $code): ?Language
    {
        return $this->interfaceCandidates()->firstWhere(Language::CODE, $code);
    }
}
