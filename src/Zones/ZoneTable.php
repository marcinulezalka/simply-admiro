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

/**
 * Tabele, które ma każda strefa z logowaniem: `<strefa>_<rodzaj>`.
 */
enum ZoneTable: string
{
    case Accounts = 'accounts';

    case LoginChallenges = 'login_challenges';

    case Sessions = 'sessions';

    public function for(ZoneDefinition|string $zone): string
    {
        return ($zone instanceof ZoneDefinition ? $zone->name : $zone) . '_' . $this->value;
    }
}
