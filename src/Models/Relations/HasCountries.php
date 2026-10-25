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
use Simplysmart\Admiro\Models\Dictionaries\Country;

/**
 * Kraje, które używają tej waluty (`locale_countries.currency_id`).
 */
trait HasCountries
{
    /**
     * @return HasMany<Country, $this>
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }
}
