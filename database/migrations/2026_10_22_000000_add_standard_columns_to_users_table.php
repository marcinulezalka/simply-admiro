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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Database\History;
use Simplysmart\Admiro\Database\IdSlots;
use Simplysmart\Admiro\Models\MainModel;

/**
 * Wyrównuje stockową tabelę `users` aplikacji do kolumn standardowych (Columns::base()/closing()) w tej samej
 * kolejności, z historią `users_history` i pulą id. Stockowa migracja i model aplikacji zostają bez zmian,
 * więc `token` jest tu nullable (stockowy model go nie nadaje); istniejące wiersze dostają token od razu.
 * Bez tabeli `users` (Core bez szkieletu) migracja nic nie robi.
 */
return new class extends Migration {
    private const string TABLE = 'users';

    private const string AFTER_DOMAIN_COLUMNS = 'remember_token';

    /**
     * Kolumny bez indeksów i kluczy, usuwane wprost w down().
     *
     * @var list<string>
     */
    private const array PLAIN_COLUMNS = [
        ColumnNames::STATUS, ColumnNames::IS_ACTIVE, ColumnNames::IS_DEFAULT, ColumnNames::SORT_ORDER,
        ColumnNames::JSON_ATTRIBUTES, ColumnNames::JSON_DEFAULT_SETTINGS, ColumnNames::JSON_TAGS,
        ColumnNames::JSON_MIGRATIONS, ColumnNames::JSON_ARCHIVE,
    ];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::table(self::TABLE, static function (Blueprint $table): void {
            $table->string(ColumnNames::TOKEN, MainModel::TOKEN_LENGTH)->nullable()->unique()->after(ColumnNames::ID);
            $table->foreignId(ColumnNames::PARENT_ID)->nullable()->after(ColumnNames::TOKEN)->constrained(self::TABLE)->nullOnDelete();
            $table->string(ColumnNames::STATUS)->nullable()->after(ColumnNames::NAME);
            $table->boolean(ColumnNames::IS_ACTIVE)->default(false)->after(ColumnNames::STATUS);
            $table->boolean(ColumnNames::IS_DEFAULT)->default(false)->after(ColumnNames::IS_ACTIVE);
            $table->integer(ColumnNames::SORT_ORDER)->default(0)->after(ColumnNames::IS_DEFAULT);
            $table->json(ColumnNames::JSON_ATTRIBUTES)->nullable()->after(self::AFTER_DOMAIN_COLUMNS);
            $table->json(ColumnNames::JSON_DEFAULT_SETTINGS)->nullable()->after(ColumnNames::JSON_ATTRIBUTES);
            $table->json(ColumnNames::JSON_TAGS)->nullable()->after(ColumnNames::JSON_DEFAULT_SETTINGS);
            $table->json(ColumnNames::JSON_MIGRATIONS)->nullable()->after(ColumnNames::JSON_TAGS);
            $table->char(ColumnNames::CHECKSUM, 64)->nullable()->index()->after(ColumnNames::JSON_MIGRATIONS);
            $table->json(ColumnNames::JSON_ARCHIVE)->nullable()->after(ColumnNames::CHECKSUM);
            $table->softDeletes(ColumnNames::DELETED_AT)->after(ColumnNames::UPDATED_AT);
        });

        foreach (DB::table(self::TABLE)->whereNull(ColumnNames::TOKEN)->pluck(ColumnNames::ID) as $id) {
            DB::table(self::TABLE)->where(ColumnNames::ID, $id)->update([ColumnNames::TOKEN => Str::random(MainModel::TOKEN_LENGTH)]);
        }

        app(History::class)->create(self::TABLE);
        app(IdSlots::class)->reserve(self::TABLE);
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        app(History::class)->drop(self::TABLE);

        Schema::table(self::TABLE, static function (Blueprint $table): void {
            $table->dropConstrainedForeignId(ColumnNames::PARENT_ID);
            $table->dropUnique([ColumnNames::TOKEN]);
            $table->dropIndex([ColumnNames::CHECKSUM]);
            $table->dropSoftDeletes(ColumnNames::DELETED_AT);
            $table->dropColumn([ColumnNames::TOKEN, ColumnNames::CHECKSUM, ...self::PLAIN_COLUMNS]);
        });
    }
};
