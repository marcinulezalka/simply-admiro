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
use Simplysmart\Admiro\Permissions\PermissionTables as T;

/**
 * Członkostwo kont stref w organizacjach (docs/baza-danych.md §15): tabela łącząca morph, wspólna dla stref,
 * z historią. Członkostwo jest niezależne od ról; usunięcie organizacji kasuje jej członków.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(T::ORGANIZATION_MEMBERS, static function (Blueprint $table): void {
            $table->id();
            $table->foreignId(T::TEAM_FOREIGN_KEY)->constrained(Organization::TABLE)->cascadeOnDelete();
            $table->morphs(T::MODEL_MORPH);
            $table->timestamps();
            $table->unique([T::TEAM_FOREIGN_KEY, T::MODEL_ID, T::MODEL_TYPE], 'organization_members_member_unique');
        });

        app(History::class)->create(T::ORGANIZATION_MEMBERS);
    }

    public function down(): void
    {
        if (Schema::hasTable(T::ORGANIZATION_MEMBERS)) {
            app(History::class)->drop(T::ORGANIZATION_MEMBERS);
        }

        Schema::dropIfExists(T::ORGANIZATION_MEMBERS);
    }
};
