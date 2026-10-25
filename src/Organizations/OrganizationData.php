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

namespace Simplysmart\Mainboard\Organizations;

use Simplysmart\Mainboard\Models\Organization;

/**
 * Dane organizacji do założenia albo zmiany (OrganizationManagement): nazwa, opcjonalna nazwa skrócona i dane firmy
 * w kolumnach z Organization::DETAIL_LENGTHS (`details`: kolumna → wartość, puste pola pominięte). NIP jest już
 * znormalizowany (Rules\Nip::normalize()).
 */
final readonly class OrganizationData
{
    /**
     * @param array<string, string> $details
     */
    public function __construct(
        public string $name,
        public ?string $shortName = null,
        public array $details = [],
    ) {}

    /**
     * Dane firmy do zapisu: każda kolumna z Organization::DETAIL_LENGTHS, brakująca jako null.
     *
     * @return array<string, string|null>
     */
    public function columns(): array
    {
        $columns = [];

        foreach (array_keys(Organization::DETAIL_LENGTHS) as $column) {
            $columns[$column] = $this->details[$column] ?? null;
        }

        return $columns;
    }

    public function get(string $column): ?string
    {
        return $this->details[$column] ?? null;
    }
}
