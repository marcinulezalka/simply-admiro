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

/**
 * Konto z zapasowym adresem e-mail: potwierdzony kodem na stronie bezpieczeństwa, pozwala usunąć zapomniany PIN
 * (nowy PIN ustawia się potem przy logowaniu kodem z głównego adresu).
 */
interface HasRecoveryEmail
{
    /**
     * Potwierdzony zapasowy adres e-mail; null, gdy konto go nie ma.
     */
    public function recoveryEmail(): ?string;
}
