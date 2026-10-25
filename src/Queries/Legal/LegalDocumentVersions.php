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

namespace Simplysmart\Mainboard\Queries\Legal;

use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Mainboard\Enums\LegalDocumentType;
use Simplysmart\Mainboard\Models\LegalDocument;

/**
 * Wersje dokumentów prawnych: bieżąca dokumentu (najwyższy opublikowany numer), szkic, opublikowane od najnowszej
 * i dokumenty, które mają co najmniej jedną opublikowaną wersję.
 */
final class LegalDocumentVersions
{
    public function current(LegalDocumentType $type): ?LegalDocument
    {
        $query = LegalDocument::query();
        $query->where('document', $type->value);
        $query->whereNotNull('version');
        $query->orderByDesc('version');

        return $query->first();
    }

    public function draft(LegalDocumentType $type): ?LegalDocument
    {
        $query = LegalDocument::query();
        $query->where('document', $type->value);
        $query->whereNull('version');

        return $query->first();
    }

    /**
     * @return Collection<int, LegalDocument>
     */
    public function published(LegalDocumentType $type): Collection
    {
        $query = LegalDocument::query();
        $query->where('document', $type->value);
        $query->whereNotNull('version');
        $query->orderByDesc('version');

        return $query->get();
    }

    /**
     * @return list<LegalDocumentType> Dokumenty z opublikowaną wersją, w kolejności przypadków enuma.
     */
    public function publishedTypes(): array
    {
        $query = LegalDocument::query();
        $query->whereNotNull('version');
        $values = $query->toBase()->distinct()->pluck('document')->all();

        return array_values(array_filter(LegalDocumentType::cases(), static fn(LegalDocumentType $type): bool => in_array($type->value, $values, true)));
    }
}
