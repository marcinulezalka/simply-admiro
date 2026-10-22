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

namespace Simplysmart\Mainboard\Enums;

use Simplysmart\Mainboard\Contracts\HasLabel;

/**
 * Kontynent kraju (kolumna `locale_countries.continent`), wyznaczany z regionu UN M49 w danych ICU:
 * Ameryki dzielą się na Północną (z Ameryką Środkową i Karaibami) i Południową, Antarktyda to tylko AQ.
 */
enum Continent: string implements HasLabel
{
    case Europe = 'europe';
    case Asia = 'asia';
    case NorthAmerica = 'north-america';
    case SouthAmerica = 'south-america';
    case Africa = 'africa';
    case Oceania = 'oceania';
    case Antarctica = 'antarctica';

    private const string ANTARCTICA_COUNTRY = 'AQ';

    private const string SOUTH_AMERICA_REGION = '005';

    private const string AREA_EUROPE = '150';

    private const string AREA_ASIA = '142';

    private const string AREA_AFRICA = '002';

    private const string AREA_OCEANIA = '009';

    private const string AREA_AMERICAS = '019';

    /**
     * Kontynent z kodów UN M49: obszaru najwyższego poziomu (150, 142, 002, 019, 009) i regionu w nim.
     */
    public static function fromRegions(string $country, string $area, string $region): ?self
    {
        return match (true) {
            $country === self::ANTARCTICA_COUNTRY => self::Antarctica,
            $area === self::AREA_EUROPE => self::Europe,
            $area === self::AREA_ASIA => self::Asia,
            $area === self::AREA_AFRICA => self::Africa,
            $area === self::AREA_OCEANIA => self::Oceania,
            $area === self::AREA_AMERICAS => $region === self::SOUTH_AMERICA_REGION ? self::SouthAmerica : self::NorthAmerica,
            default => null,
        };
    }

    public function label(): string
    {
        return text('enums.continent.' . $this->value);
    }
}
