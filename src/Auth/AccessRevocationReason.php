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

namespace Simplysmart\Admiro\Auth;

/**
 * Powód unieważnienia wszystkich sesji i zaufanych urządzeń konta (dziennik `sign_in`).
 */
enum AccessRevocationReason: string
{
    /** Właściciel zmienił PIN. */
    case PinChanged = 'pin_changed';

    /** Administrator usunął PIN; właściciel ustawi nowy przy logowaniu. */
    case PinReset = 'pin_reset';

    /** Właściciel usunął zapomniany PIN kodem z zapasowego adresu e-mail; nowy ustawi przy logowaniu. */
    case PinResetWithRecoveryEmail = 'pin_reset_recovery_email';

    /** Administrator zdjął blokadę po błędnych PIN-ach. */
    case Unlocked = 'unlocked';

    /** Administrator wyłączył konto. */
    case Deactivated = 'deactivated';

    /** Użyto starego tokenu zaufanego urządzenia (ktoś mógł skopiować ciasteczko). */
    case DeviceTokenReused = 'device_token_reused';

    /** Konto straciło rolę albo członkostwo w organizacji. */
    case PermissionsReduced = 'permissions_reduced';

    /** Właściciel wylogował konto ze wszystkich urządzeń (strona „Bezpieczeństwo”). */
    case SignedOutEverywhere = 'signed_out_everywhere';
}
