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
use Simplysmart\Mainboard\Database\IdSlots;

/**
 * Słownik języków (ISO 639): nazwa własna, ikona flagi i języki interfejsu (is_interface, w granicach APP_LOCALES).
 */
return new class extends Migration {
    private const string TABLE = 'locale_languages';

    public function up(): void
    {
        Schema::create(self::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->string('code', 8)->unique();
            $table->string('native_name')->nullable();
            $table->boolean('is_interface')->default(false);
            $table->string('flag_icon', 16)->nullable();
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
