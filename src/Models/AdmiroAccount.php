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

use Simplysmart\Mainboard\Models\ZoneAccount;

/**
 * Konto administratora (tabela z migracji Core `create_admiro_tables`).
 */
final class AdmiroAccount extends ZoneAccount
{
    protected $table = 'admiro_accounts';
}
