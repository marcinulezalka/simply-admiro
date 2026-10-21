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
use Simplysmart\Mainboard\Models\Relations\BelongsToCurrency;
use Simplysmart\Mainboard\Models\Relations\HasOfficialLanguages;
use Simplysmart\Mainboard\Models\Relations\HasTaxRates;

/**
 * Kraj (ISO 3166-1 alpha-2) z numerem kierunkowym, walutą, językami urzędowymi, członkostwem w UE
 * i stawkami VAT. Każde powiązanie jest opcjonalne. `is_default` — kraj aplikacji (jeden);
 * `sort_order` ustawia kolejność w listach wyboru (wyżej = wcześniej).
 *
 * @property string $code
 * @property string|null $dial_code
 * @property int|null $currency_id
 * @property bool $is_eu
 * @property bool $is_default
 */
final class Country extends DataModel
{
    use BelongsToCurrency;
    use HasI18n;
    use HasOfficialLanguages;
    use HasTaxRates;

    protected $table = 'dict_countries';

    /**
     * Wartości domyślne kolumn jak w migracji.
     *
     * @var array<string, mixed>
     */
    protected $attributes = ['is_eu' => false, 'is_default' => false];

    public function translationFallback(): string
    {
        return $this->code;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['code' => 'string', 'dial_code' => 'string', 'currency_id' => 'integer', 'is_eu' => 'boolean', 'is_default' => 'boolean'];
    }
}
