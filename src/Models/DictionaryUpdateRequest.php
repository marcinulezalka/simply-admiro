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

use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Enums\DictionaryUpdateStatus;

/**
 * Prośba o aktualizację słownika (`dictionary_update_requests`): słownik (`dictionary` — klucz
 * Contracts\DictionaryUpdater, `name` — jego nazwa w chwili prośby), kto prosi (`zone`, `account_id`, organizacja),
 * stan (`status`: Enums\DictionaryUpdateStatus — stan dziedzinowy), decyzja administratora (`decided_by` — konto
 * strefy administratorów, `decided_at`), zakończenie zadania (`completed_at`) i podsumowanie albo błąd (`result`).
 * Zapis tylko przez DictionaryUpdates\DictionaryUpdateRequests, odczyt przez Queries\DictionaryUpdates\DictionaryUpdateRequestList.
 *
 * @property string $dictionary
 * @property string $zone
 * @property int $account_id
 * @property int|null $organization_id
 * @property DictionaryUpdateStatus $status
 * @property int|null $decided_by
 * @property Carbon|null $decided_at
 * @property Carbon|null $completed_at
 * @property string|null $result
 */
final class DictionaryUpdateRequest extends DataModel
{
    public const string TABLE = 'dictionary_update_requests';

    protected $table = self::TABLE;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = ['status' => 'pending'];

    public function usesRecordStatus(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dictionary' => 'string',
            'zone' => 'string',
            'account_id' => 'integer',
            'organization_id' => 'integer',
            'status' => DictionaryUpdateStatus::class,
            'decided_by' => 'integer',
            'decided_at' => 'datetime',
            'completed_at' => 'datetime',
            'result' => 'string',
        ];
    }
}
