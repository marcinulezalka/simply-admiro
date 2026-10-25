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

namespace Simplysmart\Admiro\Models\Relations;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Simplysmart\Admiro\Models\Dictionaries\TaxRate;

/**
 * Stawki VAT kraju (`locale_tax_rates.country_id`).
 */
trait HasTaxRates
{
    /**
     * @return HasMany<TaxRate, $this>
     */
    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }
}
