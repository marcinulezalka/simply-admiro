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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Enums\ConsentDecision;

/**
 * Wpis rejestru zgód na cookies (tabela `cookie_consents`): tylko dopisywanie, bez kolumn standardowych i historii,
 * bez IP i danych osobowych — losowy identyfikator zgody z ciasteczka, wersja polityki, decyzja i kategorie.
 * Starsze niż `admiro.cookies.retention_days` usuwa `admiro:cookies:prune`.
 *
 * @property int $id
 * @property Carbon $recorded_at
 * @property string $consent_id
 * @property int $policy_version
 * @property ConsentDecision $decision
 * @property list<string> $categories
 */
final class CookieConsent extends Model
{
    public const string TABLE = 'cookie_consents';

    public $timestamps = false;

    protected $table = self::TABLE;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'recorded_at' => 'datetime',
            'consent_id' => 'string',
            'policy_version' => 'integer',
            'decision' => ConsentDecision::class,
            'categories' => 'array',
        ];
    }
}
