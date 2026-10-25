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

namespace Simplysmart\Mainboard\Auth\Recovery;

/**
 * Cel kodu bezpieczeństwa wysyłanego poza logowaniem (SessionCodes, Mail\SecurityCodeMail); wartość to klucz tekstów
 * `emails.security_code.<cel>.*` i część klucza sesji.
 */
enum SecurityCodePurpose: string
{
    /** Potwierdzenie nowego zapasowego adresu e-mail (kod na ten adres). */
    case RecoveryEmail = 'recovery_email';

    /** Reset zapomnianego PIN-u (kod na zapasowy adres konta). */
    case PinReset = 'pin_reset';
}
