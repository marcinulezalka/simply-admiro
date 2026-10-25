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

namespace Simplysmart\Admiro\Legal;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Models\LegalDocument;
use Simplysmart\Admiro\Queries\Legal\LegalDocumentVersions;

/**
 * Dokumenty prawne w wersjach, każdy osobno: jeden szkic dokumentu (zaczyna się od treści bieżącej wersji), zapis
 * tytułu i treści Markdown w językach interfejsu i publikacja szkicu jako kolejnej wersji (wymaga treści w języku
 * domyślnym). Opublikowanej wersji się nie zmienia.
 */
final readonly class LegalDocuments
{
    public function __construct(
        private LegalDocumentVersions $versions,
        private ConnectionInterface $db,
    ) {}

    /**
     * Szkic dokumentu: istniejący albo nowy z treścią bieżącej wersji.
     */
    public function draft(LegalDocumentType $type): LegalDocument
    {
        $draft = $this->versions->draft($type);

        if ($draft !== null) {
            return $draft;
        }

        $draft = new LegalDocument();
        $draft->forceFill([
            ColumnNames::NAME => text('legal.draft_name', ['document' => $type->label()]),
            ColumnNames::IS_ACTIVE => true,
            'document' => $type,
            ColumnNames::JSON_I18N => $this->versions->current($type)?->getAttribute(ColumnNames::JSON_I18N) ?? [],
        ])->save();

        return $draft;
    }

    /**
     * @param array<string, array{title: string, body: string}> $content Język → tytuł i treść Markdown.
     */
    public function saveDraft(LegalDocumentType $type, array $content): LegalDocument
    {
        $draft = $this->draft($type);
        $current = $draft->getAttribute(ColumnNames::JSON_I18N);
        $draft->forceFill([ColumnNames::JSON_I18N => [...(is_array($current) ? $current : []), ...$content]])->save();

        return $draft;
    }

    /**
     * @throws LegalDocumentRejected Gdy dokument nie ma szkicu albo szkic nie ma tytułu i treści w języku `$defaultLocale`.
     */
    public function publish(LegalDocumentType $type, string $defaultLocale): LegalDocument
    {
        $draft = $this->versions->draft($type) ?? throw LegalDocumentRejected::because('there is no draft to publish.');
        $content = $draft->content($defaultLocale);

        if (trim($content['title']) === '' || trim($content['body']) === '') {
            throw LegalDocumentRejected::because('the draft needs a title and content in the default language.');
        }

        return $this->db->transaction(function () use ($type, $draft): LegalDocument {
            $version = ($this->versions->current($type)->version ?? 0) + 1;
            $draft->forceFill([
                ColumnNames::NAME => text('legal.version_name', ['document' => $type->label(), 'version' => (string) $version]),
                'version' => $version,
                'published_at' => Carbon::now(),
            ])->save();

            return $draft;
        });
    }
}
