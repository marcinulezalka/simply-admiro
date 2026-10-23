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

namespace Simplysmart\Mainboard\Contracts;

use Simplysmart\Mainboard\Models\ZoneAccount;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Konto zalogowane w strefie bieżącego żądania (zawsze w jej jawnie nazwanym guardzie); jedno miejsce tej reguły
 * dla middleware, kontrolerów i layoutu.
 */
interface CurrentAccount
{
    /**
     * Konto strefy albo null (gość, strefa bez logowania, żądanie poza strefą).
     */
    public function get(): ?SignInAccount;

    /**
     * Identyfikator konta albo null.
     */
    public function id(): int|string|null;

    /**
     * Konto strefy z kontem Core (role, organizacje).
     *
     * @throws AccessDeniedHttpException Gdy żądanie nie ma takiego konta.
     */
    public function require(): ZoneAccount;
}
