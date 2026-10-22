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
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Database\Columns;
use Simplysmart\Mainboard\Database\History;
use Simplysmart\Mainboard\Database\IdSlots;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Permissions\PermissionTables as T;

/**
 * Tabele spatie/laravel-permission z zespołami = organizacjami (docs/baza-danych.md §15) i odmowy uprawnień.
 *
 * Uprawnienia i role mają pełny standard tabel danych; tabele łączące mają `id` (dla historii) zamiast
 * złożonego klucza głównego spatie, który zostaje unikalnym indeksem (z krótką nazwą: limit 64 znaków MySQL). Usunięcie organizacji kasuje jej role
 * i przypisania (rola organizacji nigdy nie staje się globalna).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(T::PERMISSIONS, static function (Blueprint $table): void {
            Columns::base($table);
            $table->string(T::GUARD_NAME);
            Columns::closing($table);
            $table->unique([ColumnNames::NAME, T::GUARD_NAME]);
        });

        Schema::create(T::ROLES, static function (Blueprint $table): void {
            Columns::base($table);
            $table->foreignId(T::TEAM_FOREIGN_KEY)->nullable()->constrained(Organization::TABLE)->cascadeOnDelete();
            $table->string(T::GUARD_NAME);
            Columns::closing($table);
            $table->unique([T::TEAM_FOREIGN_KEY, ColumnNames::NAME, T::GUARD_NAME]);
        });

        Schema::create(T::MODEL_HAS_PERMISSIONS, static function (Blueprint $table): void {
            $table->id();
            $table->foreignId(T::PERMISSION_FOREIGN_KEY)->constrained(T::PERMISSIONS)->cascadeOnDelete();
            $table->morphs(T::MODEL_MORPH);
            $table->foreignId(T::TEAM_FOREIGN_KEY)->constrained(Organization::TABLE)->cascadeOnDelete();
            $table->unique([T::TEAM_FOREIGN_KEY, T::PERMISSION_FOREIGN_KEY, T::MODEL_ID, T::MODEL_TYPE], 'model_has_permissions_assignment_unique');
        });

        Schema::create(T::MODEL_HAS_ROLES, static function (Blueprint $table): void {
            $table->id();
            $table->foreignId(T::ROLE_FOREIGN_KEY)->constrained(T::ROLES)->cascadeOnDelete();
            $table->morphs(T::MODEL_MORPH);
            $table->foreignId(T::TEAM_FOREIGN_KEY)->constrained(Organization::TABLE)->cascadeOnDelete();
            $table->unique([T::TEAM_FOREIGN_KEY, T::ROLE_FOREIGN_KEY, T::MODEL_ID, T::MODEL_TYPE], 'model_has_roles_assignment_unique');
        });

        Schema::create(T::ROLE_HAS_PERMISSIONS, static function (Blueprint $table): void {
            $table->id();
            $table->foreignId(T::PERMISSION_FOREIGN_KEY)->constrained(T::PERMISSIONS)->cascadeOnDelete();
            $table->foreignId(T::ROLE_FOREIGN_KEY)->constrained(T::ROLES)->cascadeOnDelete();
            $table->unique([T::PERMISSION_FOREIGN_KEY, T::ROLE_FOREIGN_KEY]);
        });

        Schema::create(T::PERMISSION_DENIALS, static function (Blueprint $table): void {
            $table->id();
            $table->foreignId(T::PERMISSION_FOREIGN_KEY)->constrained(T::PERMISSIONS)->cascadeOnDelete();
            $table->morphs(T::MODEL_MORPH);
            $table->foreignId(T::TEAM_FOREIGN_KEY)->constrained(Organization::TABLE)->cascadeOnDelete();
            $table->timestamps();
            $table->unique([T::TEAM_FOREIGN_KEY, T::PERMISSION_FOREIGN_KEY, T::MODEL_ID, T::MODEL_TYPE], 'permission_denials_denial_unique');
        });

        foreach (T::ALL as $name) {
            app(History::class)->create($name);
        }

        app(IdSlots::class)->reserve(T::PERMISSIONS);
        app(IdSlots::class)->reserve(T::ROLES);
    }

    public function down(): void
    {
        foreach (array_reverse(T::ALL) as $name) {
            if (Schema::hasTable($name)) {
                app(History::class)->drop($name);
            }

            Schema::dropIfExists($name);
        }
    }
};
