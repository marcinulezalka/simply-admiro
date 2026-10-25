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
use Simplysmart\Admiro\Models\Dictionaries\CountryLanguage;
use Simplysmart\Admiro\Models\Dictionaries\Language;

/**
 * Języki urzędowe kraju, główny pierwszy (`locale_country_language.is_primary`).
 */
trait HasOfficialLanguages
{
    /**
     * @return BelongsToMany<Language, $this, CountryLanguage>
     */
    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, CountryLanguage::TABLE)
            ->using(CountryLanguage::class)
            ->withPivot(CountryLanguage::IS_PRIMARY)
            ->withTimestamps()
            ->orderByPivot(CountryLanguage::IS_PRIMARY, 'desc');
    }
}
