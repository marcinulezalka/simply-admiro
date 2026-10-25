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
use Simplysmart\Mainboard\Database\CodeColumns;
use Simplysmart\Mainboard\Database\Columns;
use Simplysmart\Mainboard\Database\History;
use Simplysmart\Mainboard\Database\IdSlots;

/**
 * Słownik krajów (ISO 3166-1: alpha-2, alpha-3, numeric): numer kierunkowy, waluta, członkostwo w UE, ikona flagi
 * oraz dane terytorialne z ICU: kontynent (Enums\Continent), region UN M49 i ludność.
 */
return new class extends Migration {
    private const string TABLE = 'locale_countries';

    public function up(): void
    {
        Schema::create(self::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->char(CodeColumns::ALPHA_2, 2)->unique();
            $table->char(CodeColumns::ALPHA_3, 3)->nullable()->unique();
            $table->char(CodeColumns::NUMERIC, 3)->nullable();
            $table->string('dial_code', 8)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('locale_currencies')->nullOnDelete();
            $table->boolean('is_eu')->default(false);
            $table->string('flag_icon', 16)->nullable();
            $table->string('continent', 20)->nullable()->index();
            $table->string('region_code', 3)->nullable();
            $table->unsignedBigInteger('population')->nullable();
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
