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

namespace Simplysmart\Admiro\Holidays;

use Illuminate\Database\ConnectionInterface;
use Simplysmart\Admiro\Contracts\HolidayProvider;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\HolidaySource;
use Simplysmart\Admiro\Models\Dictionaries\Country;
use Simplysmart\Admiro\Models\Dictionaries\Holiday;

/**
 * Zapisuje święta dostawcy (HolidayProvider) kraju w roku: idempotentnie (wpis z dnia aktualizowany), bez nadpisywania
 * wpisów ręcznych; dwa święta jednego dnia mają wspólny wpis z obiema nazwami.
 */
final readonly class HolidaySync
{
    private const string NAME_SEPARATOR = ' / ';

    private const string ENGLISH = 'en';

    public function __construct(
        private HolidayProvider $provider,
        private ConnectionInterface $db,
    ) {}

    /**
     * @return int Liczba zapisanych (nowych albo zmienionych) świąt.
     *
     * @throws HolidaysUnavailable
     */
    public function sync(Country $country, int $year): int
    {
        $byDate = [];

        foreach ($this->provider->forYear($country->alpha_code_2, $year) as $quote) {
            $key = $quote->date->toDateString();
            $byDate[$key] = isset($byDate[$key])
                ? new HolidayQuote($quote->date, $byDate[$key]->localName . self::NAME_SEPARATOR . $quote->localName, $byDate[$key]->englishName . self::NAME_SEPARATOR . $quote->englishName)
                : $quote;
        }

        return $this->db->transaction(static function () use ($country, $byDate): int {
            $saved = 0;

            foreach ($byDate as $quote) {
                $query = Holiday::query();
                $query->where('country_id', $country->id);
                $query->whereDate('holiday_date', $quote->date);
                $holiday = $query->first() ?? new Holiday();

                if ($holiday->exists && $holiday->source === HolidaySource::Manual) {
                    continue;
                }

                $holiday->forceFill([
                    ColumnNames::NAME => $quote->localName,
                    ColumnNames::IS_ACTIVE => true,
                    ColumnNames::JSON_I18N => [self::ENGLISH => $quote->englishName],
                    'country_id' => $country->id,
                    'holiday_date' => $quote->date,
                    'source' => HolidaySource::Nager,
                    'is_day_off' => true,
                ]);

                if ($holiday->isDirty()) {
                    $holiday->save();
                    $saved++;
                }
            }

            return $saved;
        });
    }
}
