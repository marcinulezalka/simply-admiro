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

namespace Simplysmart\Admiro\Console;

use Simplysmart\Mainboard\Console\CreateZoneAccountCommand;

/**
 * Zakłada konto administratora: strefa nie ma rejestracji (wspólna logika w Core).
 */
final class CreateAdmiroAccount extends CreateZoneAccountCommand
{
    protected $name = 'admiro:account:create';

    protected $description = 'Zakłada konto administratora strefy admiro';

    protected function zoneName(): string
    {
        return config()->string('admiro.zone.name');
    }
}
