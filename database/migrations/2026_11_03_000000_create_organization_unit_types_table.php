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
use Simplysmart\Admiro\Database\Seeders\OrganizationUnitTypeSeeder;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\OrganizationUnitType;

/**
 * Słownik typów jednostek organizacyjnych (`organization_unit_types`: kod, prefiks numeru, ikona, struktura
 * wewnętrzna, domyślna widoczność w publicznym grafiku) z typami domyślnymi w slotach 1–999 (OrganizationUnitTypeSeeder)
 * oraz typ jednostki w organizacjach (`unit_type_id`, null — bez typu; usunięcie typu zostawia jednostkę bez typu).
 * Triggery historii organizacji są przebudowywane, więc historia zapisuje nową kolumnę, a pula id użytkowników
 * organizacji jest rezerwowana ponownie (SQLite przebudowuje tabelę przy kluczu obcym; w MySQL licznik zostaje nad
 * istniejącymi id).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(OrganizationUnitType::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->string('code', 32)->unique();
            $table->string('number_prefix', OrganizationUnitType::PREFIX_MAX_LENGTH)->default('');
            $table->string('icon', 32)->default('building');
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_public')->default(true);
            Columns::closing($table);
        });

        app(History::class)->create(OrganizationUnitType::TABLE);
        app(IdSlots::class)->reserve(OrganizationUnitType::TABLE);
        app(OrganizationUnitTypeSeeder::class)->run();

        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->foreignId(Organization::UNIT_TYPE_ID)->nullable()->after(Organization::IS_DEPARTMENT)->constrained(OrganizationUnitType::TABLE)->nullOnDelete();
        });

        app(History::class)->sync(Organization::TABLE);
        app(IdSlots::class)->reserve(Organization::TABLE);
    }

    public function down(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->dropConstrainedForeignId(Organization::UNIT_TYPE_ID);
        });

        app(History::class)->sync(Organization::TABLE);
        app(History::class)->drop(OrganizationUnitType::TABLE);
        Schema::dropIfExists(OrganizationUnitType::TABLE);
    }
};
