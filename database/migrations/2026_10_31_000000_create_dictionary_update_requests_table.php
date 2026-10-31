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
use Simplysmart\Admiro\Models\DictionaryUpdateRequest;
use Simplysmart\Admiro\Models\Organization;

/**
 * Prośby stref o aktualizację słowników (Models\DictionaryUpdateRequest): słownik po kluczu, konto strefy, które prosi
 * (bez klucza obcego — strefy mają osobne tabele kont), jego organizacja, stan i decyzja administratora.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(DictionaryUpdateRequest::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->string('dictionary', 64)->index();
            $table->string('zone', 64);
            $table->unsignedBigInteger('account_id');
            $table->foreignId('organization_id')->nullable()->constrained(Organization::TABLE)->nullOnDelete();
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('result')->nullable();
            Columns::closing($table);
        });

        app(History::class)->create(DictionaryUpdateRequest::TABLE);
        app(IdSlots::class)->reserve(DictionaryUpdateRequest::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(DictionaryUpdateRequest::TABLE);
        Schema::dropIfExists(DictionaryUpdateRequest::TABLE);
    }
};
