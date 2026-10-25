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
use Simplysmart\Mainboard\Database\History;
use Simplysmart\Mainboard\Models\Organization;

/**
 * Dane firmy organizacji (wszystkie opcjonalne): NIP, REGON, KRS, telefon, e-mail, e-mail do rozliczeń i adres
 * (długości z Organization::DETAIL_LENGTHS). NIP nie jest unikalny — oddział może mieć NIP firmy. Triggery historii
 * są przebudowywane, więc historia zapisuje nowe kolumny.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $after = Organization::SHORT_NAME;

            foreach (Organization::DETAIL_LENGTHS as $column => $length) {
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
            $table->dropColumn(array_keys(Organization::DETAIL_LENGTHS));
        });

        app(History::class)->sync(Organization::TABLE);
    }
};
