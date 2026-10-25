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

namespace Simplysmart\Admiro\Queries\Dictionaries;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Simplysmart\Admiro\Enums\TaxRateType;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\TaxRate;

/**
 * Stawki VAT obowiązujące w danym dniu: od `valid_from` do `valid_to` włącznie (bez końca, gdy null).
 * Kraj null oznacza stawki bez kraju (ogólne). Stawka aplikacji to podstawowa stawka kraju domyślnego,
 * a gdy słownik jej nie ma — admiro.localization.vat_percent.
 */
final readonly class TaxRatesInForce
{
    public function __construct(
        private DefaultEntries $defaults,
        private Repository $config,
    ) {}

    /**
     * Stawki kraju w danym dniu (domyślnie dziś): podstawowa pierwsza, potem malejąco.
     *
     * @return Collection<int, TaxRate>
     */
    public function forCountry(?Country $country, ?CarbonInterface $on = null): Collection
    {
        $rates = $this->validOn(TaxRate::query()->where('country_id', $country?->id), $on)->get();

        return $rates->toBase()->sortBy([
            static fn(TaxRate $a, TaxRate $b): int => ($b->type === TaxRateType::Standard) <=> ($a->type === TaxRateType::Standard),
            static fn(TaxRate $a, TaxRate $b): int => (float) $b->rate <=> (float) $a->rate,
        ])->values();
    }

    public function standard(?Country $country, ?CarbonInterface $on = null): ?TaxRate
    {
        $query = $this->validOn(TaxRate::query()->where('country_id', $country?->id), $on);
        $query->where('type', TaxRateType::Standard->value)->orderByDesc('valid_from');

        return $query->first();
    }

    /**
     * Procent stawki aplikacji w danym dniu („23.00”).
     */
    public function applicationPercent(?CarbonInterface $on = null): string
    {
        $rate = $this->standard($this->defaults->country(), $on)?->rate;

        return $rate ?? $this->config->string('admiro.localization.vat_percent');
    }

    /**
     * @param Builder<TaxRate> $query
     *
     * @return Builder<TaxRate>
     */
    private function validOn(Builder $query, ?CarbonInterface $on): Builder
    {
        $day = ($on ?? CarbonImmutable::today())->toDateString();

        $query->whereDate('valid_from', '<=', $day);
        $query->where(static function (Builder $query) use ($day): void {
            $query->whereNull('valid_to');
            $query->orWhereDate('valid_to', '>=', $day);
        });

        return $query;
    }
}
