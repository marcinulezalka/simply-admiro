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
use Simplysmart\Admiro\Database\IdSlots;
use Simplysmart\Admiro\Models\Organization;

/**
 * Dłuższa nazwa skrócona organizacji: kolumna `short_name` mieści Organization::SHORT_NAME_MAX_LENGTH znaków (wcześniej
 * 40). SQLite przebudowuje przy tym tabelę (znikają triggery historii i rezerwacja id od 1000), więc triggery są
 * przebudowywane (History::sync()), a pula id rezerwowana ponownie (IdSlots::reserve(); w MySQL bez skutku, gdy są już
 * wyższe id). Wycofanie przycina dłuższe nazwy do 40 znaków.
 */
return new class extends Migration {
    private const int PREVIOUS_LENGTH = 40;

    public function up(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->string(Organization::SHORT_NAME, Organization::SHORT_NAME_MAX_LENGTH)->nullable()->change();
        });

        $this->restore();
    }

    public function down(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->string(Organization::SHORT_NAME, self::PREVIOUS_LENGTH)->nullable()->change();
        });

        $this->restore();
    }

    private function restore(): void
    {
        app(History::class)->sync(Organization::TABLE);
        app(IdSlots::class)->reserve(Organization::TABLE);
    }
};
