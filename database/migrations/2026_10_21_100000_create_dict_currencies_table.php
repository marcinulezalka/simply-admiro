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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Simplysmart\Mainboard\Database\Columns;
use Simplysmart\Mainboard\Database\History;

/**
 * Słownik walut (ISO 4217): kod, liczba miejsc po przecinku, waluta domyślna aplikacji.
 */
return new class extends Migration {
    private const string TABLE = 'dict_currencies';

    public function up(): void
    {
        Schema::create(self::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->char('code', 3)->unique();
            $table->unsignedTinyInteger('decimals')->default(2);
            $table->boolean('is_default')->default(false);
            Columns::closing($table, i18n: true);
        });

        app(History::class)->create(self::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(self::TABLE);
        Schema::dropIfExists(self::TABLE);
    }
};
