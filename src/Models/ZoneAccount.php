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

namespace Simplysmart\Mainboard\Models;

use Simplysmart\Mainboard\Auth\Concerns\SignsInWithCodeAndPin;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;

/**
 * Baza modelu kont strefy logującej się kodem z e-maila i PIN-em (tabela `<strefa>_accounts`
 * z Database\ZoneTables). Model strefy ustawia tylko `$table`:
 *
 *     final class ShopAccount extends ZoneAccount
 *     {
 *         protected $table = 'shop_accounts';
 *     }
 */
abstract class ZoneAccount extends MainAuthenticatable implements CodeAndPinAccount
{
    use SignsInWithCodeAndPin;
}
