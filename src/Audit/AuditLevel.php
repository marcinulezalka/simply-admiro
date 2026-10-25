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

namespace Simplysmart\Admiro\Audit;

/**
 * Waga wpisu dziennika (metoda loggera PSR-3).
 */
enum AuditLevel: string
{
    case Info = 'info';
    case Notice = 'notice';
    case Warning = 'warning';
}
