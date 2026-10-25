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
use Simplysmart\Admiro\Models\AuditEvent;

/**
 * Dziennik zdarzeń (logowania, blokady, role, akcje administratorów) do podglądu w panelu. Tylko dopisywanie:
 * bez kolumn standardowych i bez historii (wpisy się nie zmieniają), konto i aktor jako identyfikator ze strefą
 * (bez klucza obcego, bo konta są w różnych tabelach), IP szyfrowane.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(AuditEvent::TABLE, static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('occurred_at')->index();
            $table->string('event', 64)->index();
            $table->string('level', 16);
            $table->string('zone', 64)->nullable();
            $table->string('account_id', 64)->nullable();
            $table->string('actor_zone', 64)->nullable();
            $table->string('actor_id', 64)->nullable();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->text('ip')->nullable();
            $table->json('context')->nullable();
            $table->index(['zone', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(AuditEvent::TABLE);
    }
};
