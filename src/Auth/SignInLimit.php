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

namespace Simplysmart\Mainboard\Auth;

/**
 * Limity prób logowania; wartość to klucz w `auth.limits` configu (max, decay_minutes).
 */
enum SignInLimit: string
{
    /** Prośby o kod dla pary e-mail + IP. */
    case CodeRequests = 'code_requests';

    /** Prośby o kod dla adresu e-mail z dowolnego IP (rotacja adresów). */
    case AccountCodeRequests = 'account_code_requests';

    /** Sprawdzenia kodu z jednego IP. */
    case CodeChecks = 'code_checks';

    /** Sprawdzenia kodu dla adresu e-mail z dowolnego IP. */
    case AccountCodeChecks = 'account_code_checks';

    /** Sprawdzenia PIN-u z jednego IP (konto ma własny licznik w bazie). */
    case PinChecks = 'pin_checks';
}
