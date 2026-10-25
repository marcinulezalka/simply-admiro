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

namespace Simplysmart\Admiro\Contracts;

use Simplysmart\Admiro\Cookies\CookieDescription;

/**
 * Katalog plików cookies aplikacji pokazywany w polityce i w ustawieniach cookies: Core dopisuje swoje (sesje stref,
 * zaufane urządzenie, zgoda, ustawienia wyglądu), paczki swoje w providerze.
 */
interface CookieCatalog
{
    public function add(CookieDescription $cookie): void;

    /**
     * @return list<CookieDescription>
     */
    public function all(): array;
}
