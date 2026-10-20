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

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Konto strefy, które loguje się sama (adres e-mail i stan konta).
 */
interface SignInAccount extends Authenticatable
{
    /**
     * Adres e-mail konta (odszyfrowany, znormalizowany).
     */
    public function emailAddress(): string;

    /**
     * Nazwa konta do powitań w e-mailach.
     */
    public function displayName(): string;

    /**
     * Czy konto jest aktywne i niezablokowane.
     */
    public function canSignIn(): bool;
}
