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

namespace Simplysmart\Admiro\Enums;

use Simplysmart\Admiro\Contracts\HasLabel;

/**
 * Standardowy stan wiersza w kolumnie `status` (Columns::base()) każdego modelu ze standardowymi kolumnami, który nie
 * trzyma tam stanu dziedzinowego (HasStandardColumns::usesRecordStatus()). Nowy wiersz jest aktywny. Przejścia
 * (canTransitionTo(), wykonywane tylko przez Database\RecordStatusTransitions): draft → pending → active,
 * active ↔ blocked, active → archived, dowolny → migrating → poprzedni, quarantined → active. Wiersz jest użyteczny
 * (w wyborach i procesach) tylko jako active i z `is_active`. Etykiety: `enums.record_status.<value>`.
 */
enum RecordStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Active = 'active';
    case Blocked = 'blocked';
    case Migrating = 'migrating';
    case Quarantined = 'quarantined';
    case Archived = 'archived';

    /**
     * Czy stan może przejść w `$target`. Wyjście z migracji (migrating → poprzedni) sprawdza
     * RecordStatusTransitions na podstawie zapisanego stanu sprzed migracji, nie ta metoda.
     */
    public function canTransitionTo(self $target): bool
    {
        if ($target === self::Migrating) {
            return $this !== self::Migrating;
        }

        return match ($this) {
            self::Draft => $target === self::Pending,
            self::Pending => $target === self::Active,
            self::Active => $target === self::Blocked || $target === self::Archived,
            self::Blocked, self::Quarantined => $target === self::Active,
            self::Migrating, self::Archived => false,
        };
    }

    /**
     * Czy wiersz w tym stanie bierze udział w wyborach i procesach aplikacji (razem z `is_active`).
     */
    public function isUsable(): bool
    {
        return $this === self::Active;
    }

    /**
     * Czy wiersz w tym stanie można zmieniać zwykłymi formularzami (migracja, kwarantanna i archiwum są tylko do odczytu).
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Pending, self::Active, self::Blocked], true);
    }

    /**
     * Ton plakietki stanu (klasa .ss-badge--<ton>): aktywny — success, zablokowany — warning, szkic i oczekujący — info,
     * migracja i kwarantanna — danger, archiwum — muted.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Blocked => 'warning',
            self::Draft, self::Pending => 'info',
            self::Migrating, self::Quarantined => 'danger',
            self::Archived => 'muted',
        };
    }

    public function label(): string
    {
        return text('enums.record_status.' . $this->value);
    }
}
