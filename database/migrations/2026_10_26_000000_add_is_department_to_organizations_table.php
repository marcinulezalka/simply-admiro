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
 * Flaga departamentu (`is_department`): wewnętrzna agregacja organizacji — dział jednostki nadrzędnej, który jest
 * osobnym kontekstem członków i ról, a dane trzyma jego jednostka (Queries\Organizations\OrganizationUnits).
 * Triggery historii są przebudowywane, więc historia zapisuje nową kolumnę.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->boolean(Organization::IS_DEPARTMENT)->default(false)->after(ColumnNames::PARENT_ID);
        });

        app(History::class)->sync(Organization::TABLE);
    }

    public function down(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->dropColumn(Organization::IS_DEPARTMENT);
        });

        app(History::class)->sync(Organization::TABLE);
    }
};
