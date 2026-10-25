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
use Simplysmart\Admiro\Database\History;

/**
 * Języki urzędowe krajów (jeden główny); tabela łącząca, bez kolumn standardowych, z historią.
 */
return new class extends Migration {
    private const string TABLE = 'locale_country_language';

    public function up(): void
    {
        Schema::create(self::TABLE, static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('country_id')->constrained('locale_countries')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('locale_languages')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['country_id', 'language_id']);
        });

        app(History::class)->create(self::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(self::TABLE);
        Schema::dropIfExists(self::TABLE);
    }
};
