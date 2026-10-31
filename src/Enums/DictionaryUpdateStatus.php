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
 * Stan prośby o aktualizację słownika (Models\DictionaryUpdateRequest, kolumna `status`): czeka na administratora,
 * zatwierdzona (zadanie w kolejce), wykonana, nieudana albo odrzucona. Etykiety: `enums.dictionary_update_status.<value>`.
 */
enum DictionaryUpdateStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Done = 'done';
    case Failed = 'failed';
    case Rejected = 'rejected';

    /**
     * Czy prośba jest jeszcze otwarta (czeka albo trwa) — wtedy kolejnej o ten sam słownik się nie tworzy.
     */
    public function isOpen(): bool
    {
        return $this === self::Pending || $this === self::Approved;
    }

    /**
     * Ton plakietki (klasa .ss-badge--<ton>).
     */
    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Done => 'success',
            self::Failed => 'danger',
            self::Rejected => 'muted',
        };
    }

    public function label(): string
    {
        return text('enums.dictionary_update_status.' . $this->value);
    }
}
