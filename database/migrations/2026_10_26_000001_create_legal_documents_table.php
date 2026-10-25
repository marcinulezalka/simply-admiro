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
use Simplysmart\Mainboard\Models\LegalDocument;

/**
 * Dokumenty prawne w wersjach (polityka prywatności, klauzula RODO; Enums\LegalDocumentType w `document`): szkic
 * bez numeru, publikacja nadaje kolejny numer w obrębie dokumentu; tytuł i treść Markdown w każdym języku
 * interfejsu w `json_i18n`, jak w polityce cookies.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create(LegalDocument::TABLE, static function (Blueprint $table): void {
            Columns::base($table);
            $table->string('document', 32)->index();
            $table->unsignedInteger('version')->nullable();
            $table->timestamp('published_at')->nullable();
            Columns::closing($table, i18n: true);
            $table->unique(['document', 'version']);
        });

        app(History::class)->create(LegalDocument::TABLE);
        app(IdSlots::class)->reserve(LegalDocument::TABLE);
    }

    public function down(): void
    {
        app(History::class)->drop(LegalDocument::TABLE);
        Schema::dropIfExists(LegalDocument::TABLE);
    }
};
