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

namespace Simplysmart\Admiro\Models\Dictionaries;

use Simplysmart\Admiro\Contracts\DictionaryEntry;
use Simplysmart\Admiro\Database\CodeColumns;
use Simplysmart\Admiro\Models\Concerns\HasI18n;
use Simplysmart\Admiro\Models\DataModel;
use Simplysmart\Admiro\Models\Relations\HasCountries;

/**
 * Waluta (ISO 4217: `alpha_code_3` `PLN`, `numeric_code` `985`). `decimals` to liczba cyfr jednostki podrzędnej (2 → grosze); `is_default` —
 * waluta aplikacji (jedna), `is_active` — do wyboru w cennikach i dokumentach.
 *
 * @property string $alpha_code_3
 * @property string|null $numeric_code
 * @property int $decimals
 */
final class Currency extends DataModel implements DictionaryEntry
{
    use HasCountries;
    use HasI18n;

    protected $table = 'locale_currencies';

    /**
     * Wartości domyślne kolumn jak w migracji.
     *
     * @var array<string, mixed>
     */
    protected $attributes = ['decimals' => 2];

    public static function codeColumn(): string
    {
        return CodeColumns::ALPHA_3;
    }

    public function translationFallback(): string
    {
        return $this->alpha_code_3;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [CodeColumns::ALPHA_3 => 'string', CodeColumns::NUMERIC => 'string', 'decimals' => 'integer'];
    }
}
