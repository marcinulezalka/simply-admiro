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

namespace Simplysmart\Admiro\Queries\DictionaryUpdates;

use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Admiro\Enums\DictionaryUpdateStatus;
use Simplysmart\Admiro\Models\DictionaryUpdateRequest;

/**
 * Prośby o aktualizację słowników: ostatnie (panel administratora, otwarte na górze), otwarta prośba o słownik, ostatnia
 * prośba o słownik (stan widoczny w strefie) i prośba po tokenie.
 */
final class DictionaryUpdateRequestList
{
    /** Ile ostatnich próśb pokazuje panel administratora. */
    public const int RECENT = 50;

    /**
     * @return Collection<int, DictionaryUpdateRequest>
     */
    public function recent(): Collection
    {
        $query = DictionaryUpdateRequest::query();
        $query->orderByRaw('case when status = ? then 0 else 1 end', [DictionaryUpdateStatus::Pending->value]);
        $query->orderByDesc('created_at')->orderByDesc('id');
        $query->limit(self::RECENT);

        return $query->get();
    }

    public function pendingCount(): int
    {
        return DictionaryUpdateRequest::query()->where('status', DictionaryUpdateStatus::Pending->value)->count();
    }

    public function open(string $dictionary): ?DictionaryUpdateRequest
    {
        $query = DictionaryUpdateRequest::query();
        $query->where('dictionary', $dictionary);
        $query->whereIn('status', [DictionaryUpdateStatus::Pending->value, DictionaryUpdateStatus::Approved->value]);

        return $query->first();
    }

    public function latest(string $dictionary): ?DictionaryUpdateRequest
    {
        $query = DictionaryUpdateRequest::query();
        $query->where('dictionary', $dictionary);
        $query->orderByDesc('created_at')->orderByDesc('id');

        return $query->first();
    }

    public function find(string $token): ?DictionaryUpdateRequest
    {
        return DictionaryUpdateRequest::query()->where('token', $token)->first();
    }
}
