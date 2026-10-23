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

namespace Simplysmart\Mainboard\Profile;

use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Enums\AvatarColor;
use Simplysmart\Mainboard\Models\ZoneAccount;

/**
 * Zmiana koloru awatara konta: zapis w `json_attributes` bez naruszania innych atrybutów (historia tabeli zapisuje
 * zmianę jak każdą inną).
 */
final class ChangeAvatarColor
{
    public function handle(ZoneAccount $account, AvatarColor $color): void
    {
        $attributes = $account->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $attributes = is_array($attributes) ? $attributes : [];
        $attributes[AvatarColor::ATTRIBUTE] = $color->value;

        $account->forceFill([ColumnNames::JSON_ATTRIBUTES => $attributes])->save();
    }
}
