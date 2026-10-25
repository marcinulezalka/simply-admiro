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

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Database\Eloquent\Model;
use LogicException;
use Simplysmart\Mainboard\Contracts\ZoneRegistry;

/**
 * Strefa, do której należy konto (po klasie modelu kont strefy). Akcje na kontach działają w strefie konta,
 * nie żądania, bo administrator może zarządzać kontami innej strefy.
 */
final readonly class AccountZone
{
    public function __construct(
        private ZoneRegistry $zones,
    ) {}

    /**
     * @throws LogicException Gdy model nie jest modelem kont żadnej strefy.
     */
    public function of(Model $account): ZoneDefinition
    {
        foreach ($this->zones->all() as $zone) {
            if ($zone->model !== null && $account::class === $zone->model) {
                return $zone;
            }
        }

        throw new LogicException('Model [' . $account::class . '] is not the account model of any zone.');
    }
}
