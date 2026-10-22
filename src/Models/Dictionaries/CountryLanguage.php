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

namespace Simplysmart\Mainboard\Models\Dictionaries;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Język urzędowy kraju (tabela łącząca `locale_country_language`, bez kolumn standardowych).
 *
 * @property int $country_id
 * @property int $language_id
 * @property bool $is_primary
 */
final class CountryLanguage extends Pivot
{
    public const string TABLE = 'locale_country_language';

    public const string IS_PRIMARY = 'is_primary';

    public $incrementing = true;

    protected $table = self::TABLE;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'language_id' => 'integer',
            self::IS_PRIMARY => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
