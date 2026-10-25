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

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\CountryLanguage;

/**
 * Kraje, w których język jest urzędowy (`locale_country_language`).
 */
trait SpokenInCountries
{
    /**
     * @return BelongsToMany<Country, $this, CountryLanguage>
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, CountryLanguage::TABLE)
            ->using(CountryLanguage::class)
            ->withPivot(CountryLanguage::IS_PRIMARY)
            ->withTimestamps();
    }
}
