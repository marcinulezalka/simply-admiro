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

use Simplysmart\Mainboard\Http\Middleware\EnsureAccountCanSignIn;

/**
 * Poziomy dostępu do tras strefy; każdy wyższy zawiera niższe (public ⊂ authenticated ⊂ uprawnienie).
 *
 * Uprawnienie nie jest osobnym poziomem: trasa poziomu Authenticated dokłada własne
 * `permission:<x>,<guard>`.
 */
enum AccessTier
{
    /** Każdy: niezalogowany odwiedzający i konto dowolnej strefy. */
    case Public;

    /** Tylko niezalogowani w tej strefie (logowanie); zalogowany trafia na dashboard. */
    case GuestOnly;

    /** Każde konto strefy, także z rolą bez uprawnień; sesja konta nieaktywnego lub unieważnionego jest wylogowywana. */
    case Authenticated;

    /**
     * Middleware poziomu, z jawnie nazwanym guardem strefy.
     *
     * @return list<string>
     */
    public function middleware(ZoneDefinition $zone): array
    {
        return match ($this) {
            self::Public => [],
            self::GuestOnly => ['guest:' . $zone->guard],
            self::Authenticated => ['auth:' . $zone->guard, EnsureAccountCanSignIn::class],
        };
    }

    /**
     * Czy poziom wymaga strefy z logowaniem.
     */
    public function requiresLogin(): bool
    {
        return $this !== self::Public;
    }
}
