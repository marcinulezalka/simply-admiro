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

namespace Simplysmart\Admiro\Models;

use Simplysmart\Admiro\Auth\Concerns\SignsInWithCodeAndPin;
use Simplysmart\Admiro\Contracts\CodeAndPinAccount;
use Simplysmart\Admiro\Contracts\HasZoneRoles;
use Simplysmart\Admiro\Models\Relations\HasOrganizations;
use Simplysmart\Admiro\Permissions\Concerns\HasZonePermissions;

/**
 * Baza modelu kont strefy logującej się kodem z e-maila i PIN-em (tabela `<strefa>_accounts`
 * z Database\ZoneTables), z rolami i uprawnieniami spatie w guardzie strefy oraz odmowami (HasZonePermissions).
 * Model strefy ustawia tylko `$table`:
 *
 *     final class ShopAccount extends ZoneAccount
 *     {
 *         protected $table = 'shop_accounts';
 *     }
 */
abstract class ZoneAccount extends MainAuthenticatable implements CodeAndPinAccount, HasZoneRoles
{
    use HasOrganizations;
    use HasZonePermissions;
    use SignsInWithCodeAndPin;
}
