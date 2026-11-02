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
use Simplysmart\Admiro\Models\Organization;

/**
 * Kolor organizacji (`color`, `#rrggbb`, opcjonalny) do grafik i zestawień; bez niego Organization::color() bierze kolor
 * z palety według id. Triggery historii są przebudowywane, więc historia zapisuje nową kolumnę.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->string(Organization::COLOR, Organization::DETAIL_LENGTHS[Organization::COLOR])->nullable()->after(Organization::SHORT_NAME);
        });

        app(History::class)->sync(Organization::TABLE);
    }

    public function down(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->dropColumn(Organization::COLOR);
        });

        app(History::class)->sync(Organization::TABLE);
    }
};
