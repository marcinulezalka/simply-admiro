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

namespace Simplysmart\Mainboard\Database;

use Illuminate\Database\Schema\Blueprint;
use Simplysmart\Mainboard\Models\MainModel;

/**
 * Kolumny standardowe każdej tabeli danych (docs/baza-danych.md §2): base() na początku tabeli,
 * potem kolumny domenowe, closing() na końcu.
 *
 *     Schema::create('locale_units', function (Blueprint $table): void {
 *         Columns::base($table);
 *         $table->string('code', 20)->unique();
 *         Columns::closing($table, i18n: true);
 *     });
 *
 * Zwykłe metody zamiast makr Blueprint: PHPStan i Intelephense widzą ich typy.
 */
final class Columns
{
    public static function base(Blueprint $table): void
    {
        $table->id(ColumnNames::ID);
        $table->string(ColumnNames::TOKEN, MainModel::TOKEN_LENGTH)->unique();
        $table->foreignId(ColumnNames::PARENT_ID)->nullable()->constrained($table->getTable())->nullOnDelete();
        $table->string(ColumnNames::NAME);
        $table->string(ColumnNames::STATUS)->nullable();
        $table->boolean(ColumnNames::IS_ACTIVE)->default(false);
        $table->boolean(ColumnNames::IS_DEFAULT)->default(false);
        $table->integer(ColumnNames::SORT_ORDER)->default(0);
    }

    /**
     * @param bool $i18n Kolumna `json_i18n` dla słowników z krótkimi tłumaczeniami (§7).
     */
    public static function closing(Blueprint $table, bool $i18n = false): void
    {
        $table->json(ColumnNames::JSON_ATTRIBUTES)->nullable();
        $table->json(ColumnNames::JSON_DEFAULT_SETTINGS)->nullable();
        $table->json(ColumnNames::JSON_TAGS)->nullable();

        if ($i18n) {
            $table->json(ColumnNames::JSON_I18N)->nullable();
        }

        $table->json(ColumnNames::JSON_MIGRATIONS)->nullable();
        $table->char(ColumnNames::CHECKSUM, 64)->nullable()->index();
        $table->json(ColumnNames::JSON_ARCHIVE)->nullable();
        $table->timestamp(ColumnNames::CREATED_AT)->nullable();
        $table->timestamp(ColumnNames::UPDATED_AT)->nullable();
        $table->softDeletes(ColumnNames::DELETED_AT);
    }
}
