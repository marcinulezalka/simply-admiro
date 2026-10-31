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
use Simplysmart\Admiro\Models\AccountTask;

/**
 * Linki do commitów, które realizują zadanie TODO (`json_commits`: lista adresów, jeden na repozytorium), wpisywane
 * przy przekazaniu zadania do sprawdzenia (Tasks\AccountTasks::submitForCheck()); triggery historii są przebudowywane.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table(AccountTask::TABLE, static function (Blueprint $table): void {
            $table->json(AccountTask::COMMITS)->nullable()->after('completed_at');
        });

        app(History::class)->sync(AccountTask::TABLE);
    }

    public function down(): void
    {
        Schema::table(AccountTask::TABLE, static function (Blueprint $table): void {
            $table->dropColumn(AccountTask::COMMITS);
        });

        app(History::class)->sync(AccountTask::TABLE);
    }
};
