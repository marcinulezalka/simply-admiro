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

namespace Simplysmart\Mainboard\Permissions;

/**
 * Role, które musi mieć każda strefa w pliku ról.
 */
enum RequiredRole: string
{
    /** Konto bez uprawnień: widzi tylko trasy publiczne i dla zalogowanych. */
    case Guest = 'guest';

    /** Wsparcie techniczne; domyślnie bez danych wrażliwych. */
    case Support = 'support';

    /** Właściciel strefy lub organizacji. */
    case Owner = 'owner';
}
