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

namespace Simplysmart\Admiro\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\RecordStatus;

/**
 * Casty kolumn z Columns::base() i Columns::closing(), ich wartości domyślne jako domyślne atrybuty
 * nowego modelu (jak w bazie, więc checksum nowego i odczytanego rekordu jest ta sama), odczyt
 * `json_default_settings` (defaultSetting()) oraz scope'y active(), default() i usable(). Kolumna `status` trzyma
 * standardowy stan wiersza (Enums\RecordStatus, nowy wiersz aktywny; zmiana tylko przez
 * Database\RecordStatusTransitions), chyba że model trzyma tam stan dziedzinowy — wtedy nadpisuje
 * usesRecordStatus() na false i sam podaje cast `status`.
 *
 * @method void mergeCasts(array<string, string> $casts) Metoda modelu Eloquent.
 * @method mixed getAttribute(string $key) Metoda modelu Eloquent.
 */
trait HasStandardColumns
{
    public function initializeHasStandardColumns(): void
    {
        $recordStatus = $this->usesRecordStatus();
        $this->attributes += [ColumnNames::IS_ACTIVE => false, ColumnNames::IS_DEFAULT => false, ColumnNames::SORT_ORDER => 0];

        if ($recordStatus) {
            $this->attributes += [ColumnNames::STATUS => RecordStatus::Active->value];
        }

        $this->mergeCasts([
            ColumnNames::TOKEN => 'string',
            ColumnNames::PARENT_ID => 'integer',
            ColumnNames::NAME => 'string',
            ColumnNames::STATUS => $recordStatus ? RecordStatus::class : 'string',
            ColumnNames::IS_ACTIVE => 'boolean',
            ColumnNames::IS_DEFAULT => 'boolean',
            ColumnNames::SORT_ORDER => 'integer',
            ColumnNames::JSON_ATTRIBUTES => 'array',
            ColumnNames::JSON_DEFAULT_SETTINGS => 'array',
            ColumnNames::JSON_TAGS => 'array',
            ColumnNames::JSON_MIGRATIONS => 'array',
            ColumnNames::CHECKSUM => 'string',
            ColumnNames::JSON_ARCHIVE => 'array',
            ColumnNames::CREATED_AT => 'datetime',
            ColumnNames::UPDATED_AT => 'datetime',
            ColumnNames::DELETED_AT => 'datetime',
        ]);
    }

    /**
     * Wartość domyślna dla procesów aplikacji z `json_default_settings` (klucz z kropkami, np. `invoice.due_days`);
     * `$default`, gdy rekord jej nie ma.
     */
    public function defaultSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->getAttribute(ColumnNames::JSON_DEFAULT_SETTINGS), $key, $default);
    }

    /**
     * Czy `status` trzyma standardowy stan wiersza (RecordStatus); model ze stanem dziedzinowym zwraca false.
     */
    public function usesRecordStatus(): bool
    {
        return true;
    }

    /**
     * Stan wiersza (pusty `status` sprzed uzupełnienia liczy się jako aktywny); null dla modelu ze stanem dziedzinowym.
     */
    public function recordStatus(): ?RecordStatus
    {
        if (! $this->usesRecordStatus()) {
            return null;
        }

        $status = $this->getAttribute(ColumnNames::STATUS);

        return $status instanceof RecordStatus ? $status : RecordStatus::Active;
    }

    /**
     * Wiersze użyteczne w wyborach i procesach: aktywny stan wiersza (RecordStatus::Active, pusty sprzed uzupełnienia
     * też) i włączone przez administratora (`is_active`); o tym, czy filtrować, decyduje klasa zapytań.
     *
     * @param Builder<static> $query
     */
    #[Scope]
    protected function usable(Builder $query): void
    {
        $status = $query->qualifyColumn(ColumnNames::STATUS);
        $query->where(static fn(Builder $state) => $state->where($status, RecordStatus::Active->value)->orWhereNull($status));
        $query->where($query->qualifyColumn(ColumnNames::IS_ACTIVE), true);
    }

    /**
     * Rekordy włączone przez administratora; o tym, czy filtrować, decyduje klasa zapytań.
     *
     * @param Builder<static> $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where($query->qualifyColumn(ColumnNames::IS_ACTIVE), true);
    }

    /**
     * Wpis domyślny tabeli (`is_default`, np. kraj aplikacji); o tym, czy filtrować, decyduje klasa zapytań.
     *
     * @param Builder<static> $query
     */
    #[Scope]
    protected function default(Builder $query): void
    {
        $query->where($query->qualifyColumn(ColumnNames::IS_DEFAULT), true);
    }
}
