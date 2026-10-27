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

namespace Simplysmart\Admiro\Organizations;

use Closure;

/**
 * Rejestr plakietek organizacji (singleton): paczka dodaje w providerze dostawcę, który dla listy organizacji podaje
 * plakietki z faktami ze swojej dziedziny (np. powiązania między organizacjami), a panel administratora pokazuje je
 * w drzewie organizacji bez znajomości tej paczki. Dostawca dostaje id organizacji i zwraca tylko te, które mają
 * plakietki.
 */
final class OrganizationBadges
{
    /**
     * @var list<Closure(list<int>): array<int, list<array{label: string, tone: string}>>>
     */
    private array $providers = [];

    /**
     * @param Closure(list<int>): array<int, list<array{label: string, tone: string}>> $provider Id organizacji →
     *                                                                                          plakietki (tekst i ton .ss-badge--<ton>).
     */
    public function add(Closure $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Plakietki wszystkich dostawców dla organizacji (bez plakietek: brak klucza).
     *
     * @param list<int> $organizationIds
     *
     * @return array<int, list<array{label: string, tone: string}>>
     */
    public function of(array $organizationIds): array
    {
        $badges = [];

        if ($organizationIds === []) {
            return $badges;
        }

        foreach ($this->providers as $provider) {
            foreach ($provider($organizationIds) as $id => $list) {
                $badges[$id] = [...$badges[$id] ?? [], ...$list];
            }
        }

        return $badges;
    }
}
