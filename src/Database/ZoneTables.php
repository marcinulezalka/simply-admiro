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

namespace Simplysmart\Mainboard\Database;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Simplysmart\Mainboard\Auth\AccountColumns;
use Simplysmart\Mainboard\Zones\ZoneTable;

/**
 * Schemat tabel strefy z logowaniem (konta, kody logowania, sesje), jednakowy dla każdej strefy.
 *
 * Migracja strefy w Core: `app(ZoneTables::class)->create('shop')` w up() i `->drop('shop')` w down().
 * E-mail, IP i user agent są szyfrowane (RODO); konto wyszukuje się po `email_hash`.
 */
final readonly class ZoneTables
{
    public const string ACCOUNT_FOREIGN_KEY = 'account_id';

    public function __construct(
        private DatabaseManager $connections,
    ) {}

    public function create(string $zone): void
    {
        $schema = $this->schema();

        $schema->create(ZoneTable::Accounts->for($zone), static function (Blueprint $table): void {
            self::accountColumns($table);
        });

        $schema->create(ZoneTable::LoginChallenges->for($zone), static function (Blueprint $table) use ($zone): void {
            self::loginChallengeColumns($table, ZoneTable::Accounts->for($zone));
        });

        $schema->create(ZoneTable::Sessions->for($zone), static function (Blueprint $table): void {
            self::sessionColumns($table);
        });
    }

    public function drop(string $zone): void
    {
        $schema = $this->schema();

        foreach ([ZoneTable::Sessions, ZoneTable::LoginChallenges, ZoneTable::Accounts] as $table) {
            $schema->dropIfExists($table->for($zone));
        }
    }

    public static function accountColumns(Blueprint $table): void
    {
        $table->id();
        $table->string(AccountColumns::TOKEN, 64)->unique();
        $table->string(AccountColumns::NAME);
        $table->text(AccountColumns::EMAIL);
        $table->string(AccountColumns::EMAIL_HASH, 64)->unique();
        $table->string(AccountColumns::PIN_HASH)->nullable();
        $table->timestamp(AccountColumns::PIN_SET_AT)->nullable();
        $table->unsignedSmallInteger(AccountColumns::PIN_FAILED_ATTEMPTS)->default(0);
        $table->unsignedTinyInteger(AccountColumns::LOCK_LEVEL)->default(0);
        $table->timestamp(AccountColumns::LOCKED_UNTIL)->nullable();
        $table->unsignedInteger(AccountColumns::AUTH_EPOCH)->default(0);
        $table->boolean(AccountColumns::IS_ACTIVE)->default(true);
        $table->timestamp(AccountColumns::LAST_LOGIN_AT)->nullable();
        $table->text(AccountColumns::LAST_LOGIN_IP)->nullable();
        $table->timestamps();
    }

    public static function loginChallengeColumns(Blueprint $table, string $accountsTable): void
    {
        $table->ulid('id')->primary();
        $table->foreignId(self::ACCOUNT_FOREIGN_KEY)->constrained($accountsTable)->cascadeOnDelete();
        $table->string('code_hash', 64);
        $table->unsignedTinyInteger('attempts')->default(0);
        $table->timestamp('expires_at');
        $table->timestamp('verified_at')->nullable();
        $table->timestamp('consumed_at')->nullable();
        $table->text('ip')->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamp('created_at')->nullable();
        $table->index([self::ACCOUNT_FOREIGN_KEY, 'created_at']);
    }

    /**
     * Kolumny sterownika sesji `database` (jak stockowa tabela `sessions`).
     */
    public static function sessionColumns(Blueprint $table): void
    {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    }

    private function schema(): Builder
    {
        return $this->connections->connection()->getSchemaBuilder();
    }
}
