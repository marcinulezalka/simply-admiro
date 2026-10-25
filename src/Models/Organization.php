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

namespace Simplysmart\Mainboard\Models;

/**
 * Organizacja: firma korzystająca z aplikacji (drzewo przez `parent_id`, np. firma → oddział), wspólna dla stref.
 * Jest zespołem spatie: role kont stref są przypisywane w obrębie organizacji (docs/baza-danych.md §15).
 *
 * Organizacja operatora aplikacji (OPERATOR_ID, seeder Core) jest zakresem stref bez firm klientów; strefa ustawia
 * ją jawnie jako bieżącą organizację spatie (domyślnej nie ma). `short_name` — opcjonalna nazwa skrócona (np. druga
 * linia pod imieniem i nazwiskiem w menu), odczytywana przez shortName().
 *
 * @property ?string $short_name
 */
final class Organization extends DataModel
{
    public const string TABLE = 'organizations';

    /** Stałe id organizacji operatora z puli seederów (IdSlots, 1–999). */
    public const int OPERATOR_ID = 1;

    public const string SHORT_NAME = 'short_name';

    public const int SHORT_NAME_MAX_LENGTH = 40;

    protected $table = self::TABLE;

    /**
     * Nazwa skrócona, a bez niej pełna nazwa.
     */
    public function shortName(): string
    {
        return $this->short_name ?? $this->name;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [self::SHORT_NAME => 'string'];
    }
}
