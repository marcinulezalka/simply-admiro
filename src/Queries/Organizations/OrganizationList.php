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

namespace Simplysmart\Admiro\Queries\Organizations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;

/**
 * Wszystkie organizacje (także wyłączone) dla paneli: lista po nazwie, wyszukiwanie (nazwa, nazwa skrócona, NIP; bez
 * rozróżniania wielkości liter), organizacja po tokenie albo po id.
 */
final class OrganizationList
{
    /**
     * @return Collection<int, Organization>
     */
    public function all(): Collection
    {
        $query = Organization::query();
        $query->orderBy(ColumnNames::NAME);

        return $query->get();
    }

    /**
     * @return Collection<int, Organization>
     */
    public function search(string $term, int $limit): Collection
    {
        $term = mb_strtolower(trim(str_replace(['%', '_'], ' ', $term)));

        if ($term === '') {
            return new Collection();
        }

        $needle = '%' . $term . '%';
        $query = Organization::query();
        $query->where(static function (Builder $match) use ($needle): void {
            $match->whereRaw('LOWER(' . ColumnNames::NAME . ') LIKE ?', [$needle])
                ->orWhereRaw('LOWER(' . Organization::SHORT_NAME . ') LIKE ?', [$needle])
                ->orWhere(Organization::NIP, 'like', $needle);
        });
        $query->orderBy(ColumnNames::NAME);
        $query->limit($limit);

        return $query->get();
    }

    public function find(string $token): ?Organization
    {
        $query = Organization::query();
        $query->where(ColumnNames::TOKEN, $token);

        return $query->first();
    }

    public function findById(?int $id): ?Organization
    {
        return $id === null ? null : Organization::query()->find($id);
    }
}
