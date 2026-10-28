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
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Database\Columns;
use Simplysmart\Admiro\Database\History;
use Simplysmart\Admiro\Database\IdSlots;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Enums\RecordStatus;
use Simplysmart\Admiro\Models\LegalDocument;

/**
 * Polityka cookies staje się dokumentem prawnym (LegalDocumentType::Cookies): jej wersje i szkic przechodzą
 * z `cookie_policies` do `legal_documents` (nowe id, ten sam token, numery wersji i daty; zapis przez model, więc
 * `checksum` obejmuje `document`, a pusty `status` staje się aktywny), razem z wpisami historii (przepiętymi na nowe
 * id), po czym `cookie_policies` i jej historia znikają. Zgody (`cookie_consents.policy_version`) wskazują numer
 * wersji, który się nie zmienia. Cofnięcie odtwarza tabelę i przenosi wiersze z powrotem (bez przeliczenia `checksum`).
 */
return new class extends Migration {
    private const string POLICIES = 'cookie_policies';

    public function up(): void
    {
        if (! Schema::hasTable(self::POLICIES)) {
            return;
        }

        $history = History::tableFor(self::POLICIES);

        foreach (DB::table(self::POLICIES)->orderBy('id')->get() as $row) {
            $attributes = (array) $row;
            $oldId = $attributes['id'];
            unset($attributes['id']);

            $document = (new LegalDocument())->newFromBuilder($attributes);
            $document->exists = false;
            $document->timestamps = false;
            $document->forceFill([
                'document' => LegalDocumentType::Cookies,
                ColumnNames::STATUS => $document->getAttribute(ColumnNames::STATUS) ?? RecordStatus::Active,
            ]);
            $document->save();

            DB::table($history)->where('row_id', $oldId)->orderBy('id')->get()->each(static function (object $entry) use ($document): void {
                $values = (array) $entry;
                unset($values['id']);
                DB::table(History::tableFor(LegalDocument::TABLE))->insert(['row_id' => $document->getKey()] + $values);
            });
        }

        app(History::class)->drop(self::POLICIES);
        Schema::drop(self::POLICIES);
    }

    public function down(): void
    {
        Schema::create(self::POLICIES, static function (Blueprint $table): void {
            Columns::base($table);
            $table->unsignedInteger('version')->nullable()->unique();
            $table->timestamp('published_at')->nullable();
            Columns::closing($table, i18n: true);
        });

        app(History::class)->create(self::POLICIES);
        app(IdSlots::class)->reserve(self::POLICIES);

        $documents = DB::table(LegalDocument::TABLE)->where('document', LegalDocumentType::Cookies->value);

        foreach ($documents->clone()->orderBy('id')->get() as $row) {
            $attributes = (array) $row;
            unset($attributes['id'], $attributes['document']);
            DB::table(self::POLICIES)->insert($attributes);
        }

        $documents->delete();
    }
};
