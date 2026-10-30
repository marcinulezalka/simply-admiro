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
use Simplysmart\Admiro\Models\AccountTask;

/**
 * Zadania TODO kont stref (Models\AccountTask): konto to strefa i identyfikator w jej tabeli kont (strefy mają osobne
 * tabele kont, więc bez klucza obcego), numer zgłoszenia kolejny w obrębie konta; pola zgłoszenia jak w systemie
 * ticketów — stan w `status` (Enums\TaskStatus), nazwa w `name`, tytuł, opis, termin, opóźnienie, projekt i klient.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(AccountTask::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->string('zone', 64);
            $table->unsignedBigInteger('account_id');
            $table->unsignedInteger('issue');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->date('due_on')->nullable();
            $table->unsignedInteger('delay_days')->default(0);
            $table->string('project')->nullable();
            $table->string('customer')->nullable();
            $table->timestamp('completed_at')->nullable();
            Columns::closing($table);
            $table->unique(['zone', 'account_id', 'issue']);
            $table->index(['zone', 'account_id', 'status']);
        });

        app(History::class)->create(AccountTask::TABLE);
        app(IdSlots::class)->reserve(AccountTask::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(AccountTask::TABLE);
        Schema::dropIfExists(AccountTask::TABLE);
    }
};
