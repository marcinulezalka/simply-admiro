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

use Illuminate\Database\Eloquent\Model;

/**
 * Wyszukuje konta bieżącej strefy (po indeksie e-maila, nigdy po jawnym adresie).
 */
interface AccountLocator
{
    public function findByEmail(string $email): (Model&CodeAndPinAccount)|null;
}
