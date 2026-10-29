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
 * Telefon komórkowy organizacji (obok dotychczasowego `phone` — teraz stacjonarnego) i kraj adresu (`country_code`,
 * kod alpha-2 słownika `locale_countries`). Obie kolumny opcjonalne; triggery historii są przebudowywane.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->string(Organization::MOBILE_PHONE, Organization::DETAIL_LENGTHS[Organization::MOBILE_PHONE])->nullable()->after(Organization::PHONE);
            $table->string(Organization::COUNTRY_CODE, Organization::DETAIL_LENGTHS[Organization::COUNTRY_CODE])->nullable()->after(Organization::CITY);
        });

        app(History::class)->sync(Organization::TABLE);
    }

    public function down(): void
    {
        Schema::table(Organization::TABLE, static function (Blueprint $table): void {
            $table->dropColumn([Organization::MOBILE_PHONE, Organization::COUNTRY_CODE]);
        });

        app(History::class)->sync(Organization::TABLE);
    }
};
