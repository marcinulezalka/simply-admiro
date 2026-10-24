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
use Simplysmart\Mainboard\Models\Dictionaries\Holiday;

/**
 * Święta i dni wolne od pracy krajów: ogólnokrajowe święta z publicznego API Nager.Date albo dni wolne wpisane ręcznie
 * przez administratora (np. dodatkowy dzień wolny). Jeden wpis na kraj i dzień; synchronizacja nie nadpisuje wpisu
 * ręcznego. `name` to nazwa w języku kraju, `json_i18n` — nazwa angielska.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(Holiday::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->foreignId('country_id')->constrained('locale_countries')->restrictOnDelete();
            $table->date('holiday_date');
            $table->string('source', 16);
            $table->boolean('is_day_off')->default(true);
            $table->unique(['country_id', 'holiday_date']);
            $table->index('holiday_date');
            Columns::closing($table, i18n: true);
        });

        app(History::class)->create(Holiday::TABLE);
        app(IdSlots::class)->reserve(Holiday::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(Holiday::TABLE);
        Schema::dropIfExists(Holiday::TABLE);
    }
};
