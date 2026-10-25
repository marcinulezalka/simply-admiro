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

namespace Simplysmart\Admiro\Auth\SignIn;

/**
 * Wynik sprawdzenia kodu z e-maila: poprawny, błędny albo bez aktywnego kodu (wygasł, wyczerpał próby).
 */
enum CodeCheckResult
{
    case Valid;

    case Invalid;

    /** Brak aktywnego kodu, kod wygasł albo wyczerpał próby. */
    case Exhausted;
}
