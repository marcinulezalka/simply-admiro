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

namespace Simplysmart\Admiro\Models;

use Simplysmart\Admiro\Models\Relations\BelongsToParentOrganization;

/**
 * Organizacja: firma korzystająca z aplikacji (drzewo przez `parent_id`, np. firma → oddział), wspólna dla stref.
 * Jest zespołem spatie: role kont stref są przypisywane w obrębie organizacji (docs/baza-danych.md §15).
 *
 * Organizacja operatora aplikacji (OPERATOR_ID, seeder Core) jest zakresem stref bez firm klientów; strefa ustawia
 * ją jawnie jako bieżącą organizację spatie (domyślnej nie ma). Dane firmy są opcjonalne: nazwa skrócona (druga linia
 * pod imieniem i nazwiskiem w menu, shortName()), NIP (10 cyfr, Rules\Nip; oddział może mieć NIP firmy), REGON, KRS,
 * adres, telefon, e-mail i e-mail do rozliczeń (przy zakładaniu bez niego — e-mail organizacji).
 *
 * Departament (`is_department`, isDepartment()) to wewnętrzna agregacja: dział jednostki nadrzędnej (organizacji bez
 * flagi), z własnymi członkami i rolami, ale bez własnych danych — dane trzyma jego jednostka
 * (Queries\Organizations\OrganizationUnits). Departament nie ma pod sobą innych organizacji.
 *
 * @property bool $is_department
 * @property-read Organization|null $parent
 * @property ?string $short_name
 * @property ?string $nip
 * @property ?string $regon
 * @property ?string $krs
 * @property ?string $street
 * @property ?string $postal_code
 * @property ?string $city
 * @property ?string $phone
 * @property ?string $email
 * @property ?string $billing_email
 */
final class Organization extends DataModel
{
    use BelongsToParentOrganization;

    public const string TABLE = 'organizations';

    /** Stałe id organizacji operatora z puli seederów (IdSlots, 1–999). */
    public const int OPERATOR_ID = 1;

    public const string SHORT_NAME = 'short_name';

    public const string IS_DEPARTMENT = 'is_department';

    public const int SHORT_NAME_MAX_LENGTH = 40;

    public const string NIP = 'nip';

    public const string REGON = 'regon';

    public const string KRS = 'krs';

    public const string STREET = 'street';

    public const string POSTAL_CODE = 'postal_code';

    public const string CITY = 'city';

    public const string PHONE = 'phone';

    public const string EMAIL = 'email';

    public const string BILLING_EMAIL = 'billing_email';

    /**
     * Kolumny danych firmy (bez nazwy i nazwy skróconej) z najwyższą długością, w kolejności formularza.
     *
     * @var array<string, int>
     */
    public const array DETAIL_LENGTHS = [
        self::NIP => 10,
        self::REGON => 14,
        self::KRS => 10,
        self::PHONE => 32,
        self::EMAIL => 255,
        self::BILLING_EMAIL => 255,
        self::STREET => 255,
        self::POSTAL_CODE => 10,
        self::CITY => 100,
    ];

    protected $table = self::TABLE;

    protected $attributes = [self::IS_DEPARTMENT => false];

    public function isDepartment(): bool
    {
        return $this->getAttribute(self::IS_DEPARTMENT) === true;
    }

    /**
     * Nazwa skrócona, a bez niej pełna nazwa.
     */
    public function shortName(): string
    {
        return $this->short_name ?? $this->name;
    }

    /**
     * Nazwa w przełączniku kontekstu: departament z nazwą skróconą jednostki („Jednostka · Departament”), bo nazwy
     * departamentów powtarzają się między jednostkami; `$short` bierze nazwy skrócone (menu konta).
     */
    public function contextName(bool $short = false): string
    {
        $name = $short ? $this->shortName() : $this->name;

        return $this->isDepartment() && $this->parent !== null ? $this->parent->shortName() . ' · ' . $name : $name;
    }

    /**
     * Adres w jednej linii (pomija puste części).
     */
    public function address(): string
    {
        return implode(', ', array_filter([$this->street, trim(($this->postal_code ?? '') . ' ' . ($this->city ?? ''))]));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [self::SHORT_NAME => 'string', self::IS_DEPARTMENT => 'boolean', ...array_fill_keys(array_keys(self::DETAIL_LENGTHS), 'string')];
    }
}
