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

use Carbon\CarbonImmutable;
use Simplysmart\Admiro\Enums\HolidaySource;
use Simplysmart\Admiro\Models\Concerns\HasI18n;
use Simplysmart\Admiro\Models\DataModel;
use Simplysmart\Admiro\Models\Relations\BelongsToCountry;

/**
 * Święto albo dzień wolny kraju w dniu `holiday_date`: `name` w języku kraju, `json_i18n` z nazwą angielską,
 * `is_day_off` — dzień wolny od pracy (kalendarze oznaczają go, a dni robocze go pomijają).
 *
 * @property int $country_id
 * @property CarbonImmutable $holiday_date
 * @property HolidaySource $source
 * @property bool $is_day_off
 * @property-read Country $country
 */
final class Holiday extends DataModel
{
    use BelongsToCountry;
    use HasI18n;

    public const string TABLE = 'locale_holidays';

    protected $table = self::TABLE;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = ['is_day_off' => true];

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
            'holiday_date' => 'immutable_date',
            'source' => HolidaySource::class,
            'is_day_off' => 'boolean',
        ];
    }
}
