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

namespace Simplysmart\Mainboard\Enums;

use Simplysmart\Mainboard\Contracts\HasLabel;

/**
 * Skąd jest święto albo dzień wolny: kalendarz świąt Nager.Date albo wpis ręczny administratora.
 */
enum HolidaySource: string implements HasLabel
{
    case Nager = 'nager';
    case Manual = 'manual';

    public function label(): string
    {
        return text('enums.holiday_source.' . $this->value);
    }
}
