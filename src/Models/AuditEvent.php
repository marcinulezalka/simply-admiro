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

use Illuminate\Database\Eloquent\Model;

/**
 * Wpis dziennika zdarzeń (tabela `audit_events`): tylko dopisywanie, bez kolumn standardowych i historii,
 * IP szyfrowane, kontekst bez danych osobowych. Starsze niż `mainboard.audit.retention_days` usuwa
 * `mainboard:audit:prune`.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $occurred_at
 * @property string $event
 * @property string $level
 * @property string|null $zone
 * @property string|null $account_id
 * @property string|null $actor_zone
 * @property string|null $actor_id
 * @property int|null $organization_id
 * @property string|null $ip
 * @property array<string, scalar|null>|null $context
 */
final class AuditEvent extends Model
{
    public const string TABLE = 'audit_events';

    public $timestamps = false;

    protected $table = self::TABLE;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'occurred_at' => 'datetime',
            'event' => 'string',
            'level' => 'string',
            'zone' => 'string',
            'account_id' => 'string',
            'actor_zone' => 'string',
            'actor_id' => 'string',
            'organization_id' => 'integer',
            'ip' => 'encrypted',
            'context' => 'array',
        ];
    }
}
