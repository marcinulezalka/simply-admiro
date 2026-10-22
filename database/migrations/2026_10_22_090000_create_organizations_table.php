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
use Simplysmart\Mainboard\Models\Organization;

/**
 * Organizacje (firmy korzystające z aplikacji), zakres przypisań ról spatie.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(Organization::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            Columns::closing($table);
        });

        app(History::class)->create(Organization::TABLE);
        app(IdSlots::class)->reserve(Organization::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(Organization::TABLE);
        Schema::dropIfExists(Organization::TABLE);
    }
};
