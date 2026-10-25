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
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Database\History;
use Simplysmart\Admiro\Models\Organization;

/**
 * Nazwa skrócona organizacji (`short_name`, opcjonalna): krótka nazwa do miejsc z małą ilością miejsca, np. druga
 * linia pod imieniem i nazwiskiem w menu aplikacji; bez niej wyświetlana jest pełna nazwa. Triggery historii są
 * przebudowywane, więc historia zapisuje nową kolumnę.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->string(Organization::SHORT_NAME, Organization::SHORT_NAME_MAX_LENGTH)->nullable()->after(ColumnNames::NAME);
        });

        app(History::class)->sync(Organization::TABLE);
    }

    public function down(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->dropColumn(Organization::SHORT_NAME);
        });

        app(History::class)->sync(Organization::TABLE);
    }
};
