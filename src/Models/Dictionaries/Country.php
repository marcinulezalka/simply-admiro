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
use Simplysmart\Admiro\Enums\Continent;
use Simplysmart\Admiro\Models\Concerns\HasI18n;
use Simplysmart\Admiro\Models\DataModel;
use Simplysmart\Admiro\Models\Relations\BelongsToCurrency;
use Simplysmart\Admiro\Models\Relations\HasOfficialLanguages;
use Simplysmart\Admiro\Models\Relations\HasTaxRates;

/**
 * Kraj (ISO 3166-1: `alpha_code_2` `PL`, `alpha_code_3` `POL`, `numeric_code` `616`) z numerem kierunkowym, walutą, językami urzędowymi, członkostwem w UE
 * i stawkami VAT. Każde powiązanie jest opcjonalne. `is_default` — kraj aplikacji (jeden);
 * `sort_order` ustawia kolejność w listach wyboru (wyżej = wcześniej). `flag_icon` — kod ikony flagi
 * w zestawie flag-icons (`pl`, `gb-eng`, `eu`). Dane terytorialne z ICU: kontynent, region UN M49 (`151` —
 * Europa Wschodnia) i ludność; `sort_order` grupuje listy kontynentami.
 *
 * @property string $alpha_code_2
 * @property string|null $alpha_code_3
 * @property string|null $numeric_code
 * @property string|null $dial_code
 * @property int|null $currency_id
 * @property bool $is_eu
 * @property string|null $flag_icon
 * @property Continent|null $continent
 * @property string|null $region_code
 * @property int|null $population
 */
final class Country extends DataModel implements DictionaryEntry
{
    use BelongsToCurrency;
    use HasI18n;
    use HasOfficialLanguages;
    use HasTaxRates;

    protected $table = 'locale_countries';

    /**
     * Wartości domyślne kolumn jak w migracji.
     *
     * @var array<string, mixed>
     */
    protected $attributes = ['is_eu' => false];

    public static function codeColumn(): string
    {
        return CodeColumns::ALPHA_2;
    }

    public function translationFallback(): string
    {
        return $this->alpha_code_2;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            CodeColumns::ALPHA_2 => 'string',
            CodeColumns::ALPHA_3 => 'string',
            CodeColumns::NUMERIC => 'string',
            'dial_code' => 'string',
            'currency_id' => 'integer',
            'is_eu' => 'boolean',
            'flag_icon' => 'string',
            'continent' => Continent::class,
            'region_code' => 'string',
            'population' => 'integer',
        ];
    }
}
