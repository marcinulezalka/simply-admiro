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
 * Zmiana profilu przez właściciela konta: imię i nazwisko, kolor awatara i język interfejsu (null = bez wyboru).
 * Atrybuty zapisuje w `json_attributes` bez naruszania innych (historia tabeli zapisuje zmianę jak każdą inną).
 * Adresu e-mail nie zmienia: to identyfikator logowania, zmienia go administrator.
 */
final class UpdateProfile
{
    public function handle(ZoneAccount $account, string $name, AvatarColor $color, ?string $locale): void
    {
        $attributes = $account->getAttribute(ColumnNames::JSON_ATTRIBUTES);
        $attributes = is_array($attributes) ? $attributes : [];
        $attributes[AvatarColor::ATTRIBUTE] = $color->value;

        if ($locale === null) {
            unset($attributes[AccountLocale::ATTRIBUTE]);
        } else {
            $attributes[AccountLocale::ATTRIBUTE] = $locale;
        }

        $account->forceFill([ColumnNames::NAME => $name, ColumnNames::JSON_ATTRIBUTES => $attributes])->save();
    }
}
