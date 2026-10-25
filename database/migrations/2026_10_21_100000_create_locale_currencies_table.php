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
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Simplysmart\Admiro\Database\CodeColumns;
use Simplysmart\Admiro\Database\Columns;
use Simplysmart\Admiro\Database\History;
use Simplysmart\Admiro\Database\IdSlots;

/**
 * Słownik walut (ISO 4217: kod literowy i numeryczny) z liczbą miejsc po przecinku.
 */
return new class extends Migration {
    private const string TABLE = 'locale_currencies';

    public function up(): void
    {
        Schema::create(self::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->char(CodeColumns::ALPHA_3, 3)->unique();
            $table->char(CodeColumns::NUMERIC, 3)->nullable();
            $table->unsignedTinyInteger('decimals')->default(2);
            Columns::closing($table, i18n: true);
        });

        app(History::class)->create(self::TABLE);
        app(IdSlots::class)->reserve(self::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(self::TABLE);
        Schema::dropIfExists(self::TABLE);
    }
};
