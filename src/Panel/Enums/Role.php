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

namespace Simplysmart\Admiro\Panel\Enums;

/**
 * Role strefy administratorów (definicje w pliku ról Core, etykiety w `roles.admiro.*`).
 */
enum Role: string
{
    /** Konto bez uprawnień. */
    case Guest = 'guest';

    /** Wsparcie techniczne: podgląd. */
    case Support = 'support';

    /** Właściciel systemu: wszystko poza zarządzaniem superadministratorami i konserwacją. */
    case Owner = 'owner';

    /** Superadministrator: wszystkie uprawnienia. */
    case Hero = 'hero';
}
