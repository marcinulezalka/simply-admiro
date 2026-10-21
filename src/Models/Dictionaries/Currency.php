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

use Simplysmart\Mainboard\Models\Concerns\HasI18n;
use Simplysmart\Mainboard\Models\DataModel;
use Simplysmart\Mainboard\Models\Relations\HasCountries;

/**
 * Waluta (ISO 4217). `decimals` to liczba cyfr jednostki podrzędnej (2 → grosze); `is_default` —
 * waluta aplikacji (jedna), `is_active` — do wyboru w cennikach i dokumentach.
 *
 * @property string $code
 * @property int $decimals
 * @property bool $is_default
 */
final class Currency extends DataModel
{
    use HasCountries;
    use HasI18n;

    protected $table = 'dict_currencies';

    /**
     * Wartości domyślne kolumn jak w migracji.
     *
     * @var array<string, mixed>
     */
    protected $attributes = ['decimals' => 2, 'is_default' => false];

    public function translationFallback(): string
    {
        return $this->code;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['code' => 'string', 'decimals' => 'integer', 'is_default' => 'boolean'];
    }
}
