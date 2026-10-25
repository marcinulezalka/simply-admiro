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
use Simplysmart\Admiro\Database\Columns;
use Simplysmart\Admiro\Database\History;
use Simplysmart\Admiro\Database\IdSlots;

/**
 * Stawki VAT z okresem ważności (valid_from–valid_to włącznie, bez końca gdy null); kraj opcjonalny.
 */
return new class extends Migration {
    private const string TABLE = 'locale_tax_rates';

    public function up(): void
    {
        Schema::create(self::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->foreignId('country_id')->nullable()->constrained('locale_countries')->nullOnDelete();
            $table->string('type', 20);
            $table->decimal('rate', 5, 2)->nullable();
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->string('note')->nullable();
            $table->index(['country_id', 'type', 'valid_from']);
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
