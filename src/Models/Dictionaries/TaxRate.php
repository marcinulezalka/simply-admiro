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

use Carbon\CarbonImmutable;
use Simplysmart\Mainboard\Enums\TaxRateType;
use Simplysmart\Mainboard\Models\Concerns\HasI18n;
use Simplysmart\Mainboard\Models\DataModel;
use Simplysmart\Mainboard\Models\Relations\BelongsToCountry;

/**
 * Stawka VAT ważna od `valid_from` do `valid_to` (włącznie; bez końca, gdy null). `rate` to procent,
 * stawka zwolniona („zw”) go nie ma. Kraj jest opcjonalny: stawka bez kraju jest ogólna, a usunięcie
 * kraju zostawia jego stawki bez niego. `name` to etykieta („23%”, „zw”).
 *
 * @property int|null $country_id
 * @property TaxRateType $type
 * @property string|null $rate
 * @property CarbonImmutable $valid_from
 * @property CarbonImmutable|null $valid_to
 * @property string|null $note
 */
final class TaxRate extends DataModel
{
    use BelongsToCountry;
    use HasI18n;

    protected $table = 'dict_tax_rates';

    public function translationFallback(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'type' => TaxRateType::class,
            'rate' => 'decimal:2',
            'valid_from' => 'immutable_date',
            'valid_to' => 'immutable_date',
            'note' => 'string',
        ];
    }
}
