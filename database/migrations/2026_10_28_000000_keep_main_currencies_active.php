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

use Illuminate\Database\Migrations\Migration;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Database\Seeders\LocalizationData;
use Simplysmart\Admiro\Models\Dictionaries\Currency;

/**
 * Włączone waluty jak przy nowej instalacji: złoty i cztery najpopularniejsze (LocalizationData::ACTIVE_CURRENCIES —
 * PLN, EUR, USD, GBP, CHF) oraz waluta domyślna; pozostałe są wyłączane (nic nie jest usuwane, kursy zostają, a waluty
 * można włączyć z powrotem w kreatorze „Waluty” albo w słowniku). Zapis przez model (historia i checksum). Cofnięcie
 * nie przywraca poprzednich włączeń.
 */
return new class extends Migration {
    public function up(): void
    {
        $query = Currency::query();
        $query->where(ColumnNames::IS_ACTIVE, true);
        $query->whereNotIn('alpha_code_3', LocalizationData::ACTIVE_CURRENCIES);
        $query->where(ColumnNames::IS_DEFAULT, false);

        foreach ($query->get() as $currency) {
            $currency->forceFill([ColumnNames::IS_ACTIVE => false])->save();
        }

        $main = Currency::query();
        $main->whereIn('alpha_code_3', LocalizationData::ACTIVE_CURRENCIES);
        $main->where(ColumnNames::IS_ACTIVE, false);

        foreach ($main->get() as $currency) {
            $currency->forceFill([ColumnNames::IS_ACTIVE => true])->save();
        }
    }

    public function down(): void {}
};
