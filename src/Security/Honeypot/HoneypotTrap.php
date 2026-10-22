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

namespace Simplysmart\Mainboard\Security\Honeypot;

/**
 * Rodzaj pułapki, w którą wpadło żądanie.
 */
enum HoneypotTrap: string
{
    /** Wejście GET na adres pułapki (może być przypadkowe). */
    case RouteVisit = 'route-get';

    /** Wysłanie formularza na adres pułapki. */
    case RouteSubmit = 'route-post';

    /** Formularz z wypełnionym ukrytym polem albo wysłany za szybko. */
    case Form = 'form';

    public function bansImmediately(): bool
    {
        return $this !== self::RouteVisit;
    }
}
