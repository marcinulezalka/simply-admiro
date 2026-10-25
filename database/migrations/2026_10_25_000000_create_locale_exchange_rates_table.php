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
use Simplysmart\Mainboard\Models\Dictionaries\ExchangeRate;

/**
 * Kursy walut w złotych (PLN za 1 jednostkę waluty) z dnia publikacji: z tabeli A NBP albo wpisane ręcznie przez
 * administratora (np. przy awarii NBP). Jeden kurs na walutę i dzień; synchronizacja z NBP nie nadpisuje kursu
 * ręcznego.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(ExchangeRate::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->foreignId('currency_id')->constrained('locale_currencies')->restrictOnDelete();
            $table->date('rate_date');
            $table->decimal('rate', 14, 6);
            $table->string('source', 16);
            $table->string('table_number', 32)->nullable();
            $table->unique(['currency_id', 'rate_date']);
            $table->index('rate_date');
            Columns::closing($table);
        });

        app(History::class)->create(ExchangeRate::TABLE);
        app(IdSlots::class)->reserve(ExchangeRate::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(ExchangeRate::TABLE);
        Schema::dropIfExists(ExchangeRate::TABLE);
    }
};
