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
 * Dane firmy organizacji (wszystkie opcjonalne): NIP, REGON, KRS, telefon, e-mail, e-mail do rozliczeń i adres.
 * Lista kolumn z długościami jest zamrożona w COLUMNS (stan z tej migracji): kolejne kolumny danych firmy dodają
 * kolejne migracje, choć Organization::DETAIL_LENGTHS rośnie. NIP nie jest unikalny — oddział może mieć NIP firmy.
 * Triggery historii są przebudowywane, więc historia zapisuje nowe kolumny.
 */
return new class extends Migration {
    /**
     * @var array<string, int>
     */
    private const array COLUMNS = [
        Organization::NIP => 10,
        Organization::REGON => 14,
        Organization::KRS => 10,
        Organization::PHONE => 32,
        Organization::EMAIL => 255,
        Organization::BILLING_EMAIL => 255,
        Organization::STREET => 255,
        Organization::POSTAL_CODE => 10,
        Organization::CITY => 100,
    ];

    public function up(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $after = Organization::SHORT_NAME;

            foreach (self::COLUMNS as $column => $length) {
                $table->string($column, $length)->nullable()->after($after);
                $after = $column;
            }

            $table->index(Organization::NIP);
        });

        app(History::class)->sync(Organization::TABLE);
    }

    public function down(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->dropIndex([Organization::NIP]);
            $table->dropColumn(array_keys(self::COLUMNS));
        });

        app(History::class)->sync(Organization::TABLE);
    }
};
