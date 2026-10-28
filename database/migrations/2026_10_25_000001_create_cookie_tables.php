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
use Simplysmart\Admiro\Models\CookieConsent;

/**
 * Polityka cookies w wersjach (szkic bez numeru, publikacja nadaje kolejny numer; tytuł i treść Markdown w każdym
 * języku interfejsu w `json_i18n`) oraz rejestr zgód odwiedzających do rozliczalności RODO: losowy identyfikator
 * zgody z przeglądarki, wersja polityki, kategorie i decyzja — bez IP, przeglądarki i danych osobowych.
 */
return new class extends Migration {
    private const string POLICIES = 'cookie_policies';

    public function up(): void
    {
        Schema::create(self::POLICIES, static function (Blueprint $table): void {
            Columns::base($table);
            $table->unsignedInteger('version')->nullable()->unique();
            $table->timestamp('published_at')->nullable();
            Columns::closing($table, i18n: true);
        });

        app(History::class)->create(self::POLICIES);
        app(IdSlots::class)->reserve(self::POLICIES);

        Schema::create(CookieConsent::TABLE, static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('recorded_at')->index();
            $table->string('consent_id', 36)->index();
            $table->unsignedInteger('policy_version');
            $table->string('decision', 16);
            $table->json('categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(CookieConsent::TABLE);
        app(History::class)->drop(self::POLICIES);
        Schema::dropIfExists(self::POLICIES);
    }
};
